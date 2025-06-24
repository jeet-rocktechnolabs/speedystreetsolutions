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

namespace Magetrend\AbandonedCart\Block\Adminhtml;

/**
 * Backend queue grid container block
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Queue extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @inheritdoc
     */
    //@codingStandardsIgnoreLine
    protected function _construct()
    {
        $this->_addButtonLabel = __('');
        $this->_controller = 'queue_index';
        $this->_headerText = __('Schedules Log');
        parent::_construct();
    }

    /**
     * @inheritdoc
     */
    //@codingStandardsIgnoreLine
    protected function _prepareLayout()
    {
        $this->removeButton('add');
        return parent::_prepareLayout();
    }
}
