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

namespace Magetrend\AbandonedCart\Block\Adminhtml\Rule\Cart;

/**
 * Backend cart rule edit grid widget block
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Grid extends \Magento\Backend\Block\Widget\Grid
{

    /**
     * Returns rule grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('abandonedcart/*/cart_grid', ['_current' => true]);
    }

    /**
     * Returns rule grid row url for edit
     *
     * @param $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'abandonedcart/*/edit',
            ['rule_id' => $row->getId()]
        );
    }

    /**
     * Add Type Filter
     * @return $this
     */
    protected function _prepareCollection()
    {
        parent::_prepareCollection();
        $this->getCollection()
            ->addFieldToFilter('type', \Magetrend\AbandonedCart\Model\Rule::TYPE_ABANDONED_CART);

        return $this;
    }
}
