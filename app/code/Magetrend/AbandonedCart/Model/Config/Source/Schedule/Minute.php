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

/**
 * Minutes source
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Minute implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Returns field types as array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        $data = [];
        $data[] = [
            'label' =>  (string)__('%1 minute', 0),
            'value'  => 0
        ];
        $data[] = [
            'label' =>  (string)__('%1 minute', 1),
            'value'  => 1
        ];
        for ($i = 5; $i < 60; $i+=5) {
            $data[] = [
                'label' => $i==1?(string)__('%1 minute', 1):(string)__('%1 minutes', $i),
                'value'  => $i
            ];
        }
        $data[] = [
            'label' =>  (string)__('%1 minutes', 59),
            'value'  => 59
        ];
        return $data;
    }
}
