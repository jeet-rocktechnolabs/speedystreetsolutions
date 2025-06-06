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

namespace Magetrend\AbandonedCart\Block\Adminhtml\Widget\Grid\Column\Filter;

/**
 * Grid column filter customer group block class
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class CustomerGroupList extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select\Extended
{
    /**
     * @var \Magetrend\AbandonedCart\Model\Config\Source\CustomerGroup
     */
    public $customerGroup;

    /**
     * CustomerGroupList constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param \Magetrend\AbandonedCart\Model\Config\Source\CustomerGroup $customerGroup
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Magetrend\AbandonedCart\Model\Config\Source\CustomerGroup $customerGroup,
        array $data = []
    ) {
        $this->customerGroup = $customerGroup;
        parent::__construct($context, $resourceHelper, $data);
    }

    protected function _getOptions()
    {
        $options = $this->customerGroup->toOptionArray();
        $empty = ['value' => '', 'label' => ''];
        array_unshift($options, $empty);
        return $options;
    }

    /**
     * Retrieve condition
     *
     * @return array
     */
    public function getCondition()
    {
        $likeExpression = $this->_resourceHelper->addLikeEscape(','.$this->getValue().',', ['position' => 'any']);
        return ['like' => $likeExpression];
    }
}
