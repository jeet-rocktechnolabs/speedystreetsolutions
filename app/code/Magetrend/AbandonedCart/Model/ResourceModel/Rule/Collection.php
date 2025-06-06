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
namespace Magetrend\AbandonedCart\Model\ResourceModel\Rule;

/**
 * Rule Resource Collection
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    //@codingStandardsIgnoreLine
    protected function _construct()
    {
        $this->_init('Magetrend\AbandonedCart\Model\Rule', 'Magetrend\AbandonedCart\Model\ResourceModel\Rule');
    }

    public function addStoreIdFilter($storeId = 0)
    {
        $this->addFieldToFilter(
            'store_ids',
            [
                ['like' => '%,'.$storeId.',%'],
                ['like' => '%,0,%']
            ]
        );

        return $this;
    }

    public function addCustomerGroupIdFilter($customerGroup = -1)
    {
        $this->addFieldToFilter(
            'customer_groups',
            [
                ['like' => '%,'.$customerGroup.',%'],
                ['like' => '%,-1,%']
            ]
        );

        return $this;
    }
}
