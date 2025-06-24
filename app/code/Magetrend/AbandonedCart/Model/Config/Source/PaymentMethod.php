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
 * Payment methods source
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class PaymentMethod extends \Magento\Payment\Model\Config\Source\Allmethods
{

    public function toOptionArray()
    {
        $options = parent::toOptionArray();
        array_unshift($options, [
            'value' => 0,
            'label' => 'All Payment Methods',
        ]);

        foreach ($options as $key => $option) {
            if (!isset($option['value'])) {
                unset($options[$key]);
            }
        }

        return $options;
    }
}
