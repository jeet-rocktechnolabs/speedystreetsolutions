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
 * Backend rule edit form container block
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize blog post edit block
     *
     * @return void
     */
    //@codingStandardsIgnoreLine
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Magetrend_AbandonedCart';
        $this->_controller = 'adminhtml_rule';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Rule'));
        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ]
            ],
            -100
        );
    }

    /**
     * Retrieve text for header element depending on loaded post
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->coreRegistry->registry('abandonedcart_rule')->getId()) {
            return __("Edit Post '%1'", $this->escapeHtml($this->coreRegistry->registry('abandonedcart_rule')->getTitle()));
        } else {
            return __('New Rule');
        }
    }

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    public function _getSaveAndContinueUrl()
    {
        return $this->getUrl('abandonedcart/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '{{tab_id}}']);
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        $type = $this->getRequest()->getParam('content_type', false);
        if (!$type && $this->coreRegistry->registry('current_rule')) {
            $type = $this->coreRegistry->registry('current_rule')->getType();
        }

        if ($type == \Magetrend\AbandonedCart\Model\Rule::TYPE_BAR) {
            return $this->getUrl('*/rule/bar_index');
        }

        if ($type == \Magetrend\AbandonedCart\Model\Rule::TYPE_ABANDONED_CART) {
            return $this->getUrl('*/rule/cart_index');
        }

        if ($type == \Magetrend\AbandonedCart\Model\Rule::TYPE_FOLLOW_UP) {
            return $this->getUrl('*/rule/order_index');
        }

        return $this->getUrl('*/*/');
    }
}
