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
 * Customer group source
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class CustomerGroup extends \Magento\Customer\Model\Config\Source\Group
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();
        unset($options[0]);
        array_unshift($options, [
            'value' => 0,
            'label' => 'NOT LOGGED IN',
        ]);
        array_unshift($options, [
            'value' => -1,
            'label' => 'All Customer Groups',
        ]);

        return $options;
    }

    public function toArray()
    {
        $data = [];
        $options = $this->toOptionArray();
        foreach ($options as $option) {
            $data[$option['value']] = $option['label'];
        }

        return $data;
    }
}
