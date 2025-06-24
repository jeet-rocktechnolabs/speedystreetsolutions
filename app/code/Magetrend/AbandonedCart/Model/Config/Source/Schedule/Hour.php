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
 * Hours source
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Hour implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Returns field types as array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        $data = [];
        for ($i = 0; $i < 24; $i++) {
            $data[] = [
                'label' => $i==1?(string)__('%1 hour', 1):(string)__('%1 hours', $i),
                'value'  => $i
            ];
        }
        return $data;
    }
}
