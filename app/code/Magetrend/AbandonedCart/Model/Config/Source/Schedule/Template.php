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
 * Email template source
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Template extends \Magento\Config\Model\Config\Source\Email\Template
{
    public $templatePath = '';

    public function setDefaultTemplate($defaultTemplate = '')
    {
        $this->templatePath = $defaultTemplate;
    }

    /**
     * Returns field types as array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        $this->setPath($this->templatePath);
        return parent::toOptionArray();
    }
}
