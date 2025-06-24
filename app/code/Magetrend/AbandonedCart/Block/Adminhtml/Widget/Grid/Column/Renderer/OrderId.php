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

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;

/**
 * Grid column renderer order id block class
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class OrderId extends AbstractRenderer
{
    public $backendUrl;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        array $data = []
    ) {
        $this->backendUrl = $backendUrl;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if (!empty($row->getOrderId())) {
            $url = $this->backendUrl->getUrl('sales/order/view', ['order_id' => $row->getOrderId()]);
            return $row->getOrderId().' <a target="_blank" href="'.$url.'">('.__('View').') </a> ';
        }

        return 'N/A';
    }
}
