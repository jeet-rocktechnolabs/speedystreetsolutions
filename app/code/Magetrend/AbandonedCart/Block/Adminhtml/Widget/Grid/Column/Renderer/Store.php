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

namespace Magetrend\AbandonedCart\Block\Adminhtml\Widget\Grid\Column\Renderer;

/**
 * Grid column renderer store block class
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Store extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Store
{
    public function render(\Magento\Framework\DataObject $row)
    {
        $data = $row->getData($this->getColumn()->getIndex());
        if (empty($data) || strpos($data, ',0,') !== false) {
            $row->setData($this->getColumn()->getIndex(), ['0']);
        } else {
            $data = rtrim(trim($data, ','), ',');
            $row->setData($this->getColumn()->getIndex(), explode(',', $data));
        }
        return parent::render($row);
    }
}
