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

namespace Magetrend\AbandonedCart\Model\Config\Source;

/**
 * Cancel events source
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class CancelEvent implements \Magento\Framework\Option\ArrayInterface
{
    const EVENT_LINK_CLICKED = 'link_clicked';

    const EVENT_OUT_OF_STOCK = 'out_of_stock';

    const EVENT_OUT_OF_STOCK_ALL = 'out_of_stock_all';

    const EVENT_NEW_CART_CREATED = 'new_cart_was_created';

    const EVENT_NEW_ORDER_WAS_PLACED = 'new_order_was_placed';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->toArray();
        $optionsArray = [];
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
        return [
            self::EVENT_NEW_CART_CREATED        => __('New cart was created'),
            self::EVENT_NEW_ORDER_WAS_PLACED    => __('New order was placed'),
            self::EVENT_LINK_CLICKED            => __('A link from an email was clicked'),
            self::EVENT_OUT_OF_STOCK            => __('Some products went out of stock'),
            self::EVENT_OUT_OF_STOCK_ALL        => __('All product went out of stock'),
        ];
    }
}
