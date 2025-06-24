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

namespace Magetrend\AbandonedCart\Model\Config\Source\Schedule;

use Magetrend\AbandonedCart\Model\Queue;

/**
 * Schedule status source
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Status implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->toArray();
        $optionsArray = [
            ['value' => 0,  'label' => __('No Rule (Default)')]
        ];
        foreach ($options as $value => $label) {
            $optionsArray[] = ['value' => $value,  'label' => $label];
        }

        return $optionsArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $status = [
            Queue::STATUS_NEW => __('Pending'),
            Queue::STATUS_SENT => __('Sent'),
            Queue::STATUS_SEND_FAILED => __('Failed'),
            Queue::STATUS_SEND_FAILED_NO_EMAIL => __('No email'),
            Queue::STATUS_ORDER_WAS_PLACED => __('An order was placed'),
            Queue::STATUS_CART_NOT_FOUND => __('Cart was deleted'),
            Queue::STATUS_ORDER_NOT_FOUND => __('Order was deleted'),
            Queue::STATUS_ORDER_WAS_PAID => __('Order was paid'),
            Queue::STATUS_ANOTHER_ORDER_WAS_PLACED  => __('Another order was placed'),
            Queue::STATUS_ANOTHER_CART_WAS_CREATED  => __('Another cart was created'),
            Queue::STATUS_CART_RESTORED  => __('Customer back (cart)'),
            Queue::STATUS_ORDER_RESTORED => __('Customer back (order)'),
            Queue::STATUS_CANCELED => __('Canceled'),
            Queue::STATUS_PRODUCT_OUT_OF_STOCK => __('Some products went out of stock'),
            Queue::STATUS_ALL_OUT_OF_STOCK => __('All products out of stock'),
            Queue::STATUS_MISSED_TO_SEND => __('Missed to send'),
            Queue::STATUS_SENDING => __('Failed to send'),
        ];

        return $status;
    }
}
