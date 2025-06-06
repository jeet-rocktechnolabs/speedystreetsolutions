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

namespace Magetrend\AbandonedCart\Observer\Queue\Order\Cancel;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magetrend\AbandonedCart\Model\Config\Source\Order\CancelEvent;
use Magetrend\AbandonedCart\Model\Rule;
use Magetrend\AbandonedCart\Model\Queue;

/**
 * Order rule email cancel observer, check was order already paid
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Paid implements ObserverInterface
{
    /**
     * @var \Magetrend\AbandonedCart\Helper\Data
     */
    public $moduleHelper;

    /**
     * NewCart constructor.
     * @param \Magetrend\AbandonedCart\Helper\Data $moduleHelper
     */
    public function __construct(
        \Magetrend\AbandonedCart\Helper\Data $moduleHelper
    ) {
        $this->moduleHelper = $moduleHelper;
    }

    /**
     * Look up for new cart, and cancel message if new cart is created.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(Observer $observer)
    {
        $rule = $observer->getRule();
        $queue = $observer->getQueue();
        if (!$queue->canSend() || !in_array(CancelEvent::EVENT_ORDER_WAS_PAID, $rule->getCancelEvents())) {
            return;
        }

        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $order = $observer->getOrder();
        if ($order->getTotalInvoiced() > 0 || $order->getState() == 'complete') {
            $queue->updateStatus(Queue::STATUS_ORDER_WAS_PAID);
        }
    }
}
