<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.3 or later
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */

namespace Magetrend\AbandonedCart\Model\Manager;

/**
 * Queue
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Queue
{
    public $collectionFactory;

    /**
     * Cart constructor.
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Magetrend\AbandonedCart\Model\ResourceModel\Queue\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    public function massStatusChange($idList, $newStatus)
    {
        if (empty($idList)) {
            return false;
        }

        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('entity_id', ['in' => $idList]);

        if ($collection->getSize() == 0) {
            return false;
        }

        foreach ($collection as $item) {
            $item->setStatus($newStatus);
        }

        $collection->walk('save');
        return true;
    }

    public function massDelete($idList)
    {
        if (empty($idList)) {
            return false;
        }

        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('entity_id', ['in' => $idList])
            ->walk('delete');

        return true;
    }
}
