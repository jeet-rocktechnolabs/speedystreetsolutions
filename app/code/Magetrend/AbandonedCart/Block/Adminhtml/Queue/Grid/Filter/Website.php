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

namespace Magetrend\AbandonedCart\Block\Adminhtml\Queue\Grid\Filter;

use Magento\Store\Model\ResourceModel\Website\Collection;

/**
 * Grid column website filter block
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Website extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    /**
     * Website collection
     *
     * @var Collection
     */
    private $websiteCollection = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \Magento\Store\Model\ResourceModel\Website\CollectionFactory
     */
    public $websitesFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websitesFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websitesFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->storeManager = $storeManager;
        $this->websitesFactory = $websitesFactory;
        parent::__construct($context, $resourceHelper, $data);
    }

    /**
     * Get options for grid filter
     *
     * @return array
     */
    //@codingStandardsIgnoreLine
    protected function _getOptions()
    {
        $result = $this->getCollection()->toOptionArray();
        array_unshift($result, ['label' => null, 'value' => null]);
        return $result;
    }

    /**
     * @return Collection|null
     */
    public function getCollection()
    {
        if ($this->websiteCollection === null) {
            $this->websiteCollection = $this->websitesFactory->create()->load();
        }

        $this->coreRegistry->register('website_collection', $this->websiteCollection);

        return $this->websiteCollection;
    }

    /**
     * Get options for grid filter
     *
     * @return null|array
     */
    public function getCondition()
    {
        $id = $this->getValue();
        if (!$id) {
            return null;
        }

        $website = $this->storeManager->getWebsite($id);
        return ['in' => $website->getStoresIds(true)];
    }
}
