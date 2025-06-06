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

namespace Magetrend\AbandonedCart\Block\Adminhtml\Rule;

/**
 * Backend order rule grid container block
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Order extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var \Magetrend\AbandonedCart\Model\Config\Source\Type
     */
    public $ruleType;

    /**
     * Rule constructor.
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magetrend\AbandonedCart\Model\Config\Source\RuleType $ruleType
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magetrend\AbandonedCart\Model\Config\Source\RuleType $ruleType,
        array $data = []
    ) {
        $this->ruleType = $ruleType;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    //@codingStandardsIgnoreLine
    protected function _construct()
    {
        $this->_addButtonLabel = __('Add New Rule');
        $this->_controller = 'rule_index';
        parent::_construct();
    }

    /**
     * @return string
     */
    public function getCreateUrl()
    {
        return  $this->getUrl(
            'abandonedcart/*/new',
            ['content_type' => \Magetrend\AbandonedCart\Model\Rule::TYPE_FOLLOW_UP]
        );
    }
}
