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
 * Grid column renderer rule type block class
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class RuleType extends AbstractRenderer
{

    /**
     * @var \Magetrend\AbandonedCart\Model\Config\Source\RuleType
     */
    public $ruleType;

    /**
     * RuleType constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Magetrend\AbandonedCart\Model\Config\Source\RuleType $ruleType
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magetrend\AbandonedCart\Model\Config\Source\RuleType $ruleType,
        array $data = []
    ) {
        $this->ruleType = $ruleType;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return mixed
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $options = $this->ruleType->toArray();
        return $options[$row->getType()];
    }
}
