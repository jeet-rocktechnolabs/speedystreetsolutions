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

namespace Magetrend\AbandonedCart\Block\Adminhtml\Rule\Edit\Tab\Bar;

use \Magento\Backend\Block\Widget\Form\Generic;
use \Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Bckend Bar Rule Edit General Tab Block
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class General extends \Magetrend\AbandonedCart\Block\Adminhtml\Rule\Edit\Tab\General
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    public $systemStore;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    public $yesNo;

    /**
     * @var \Magento\Customer\Model\Config\Source\Group
     */
    public $customerGroup;

    /**
     * @var \Magetrend\AbandonedCart\Model\Config\Source\PaymentMethod
     */
    public $paymentMethod;

    /**
     * @var \Magetrend\AbandonedCart\Model\Config\Source\Order\Event
     */
    public $triggerEvents;

    /**
     * General constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Config\Model\Config\Source\Yesno $yesno,
        \Magetrend\AbandonedCart\Model\Config\Source\CustomerGroup $customerGroup,
        \Magetrend\AbandonedCart\Model\Config\Source\PaymentMethod $paymentMethod,
        \Magetrend\AbandonedCart\Model\Config\Source\Bar\Event $triggerEvents,
        array $data = []
    ) {
        $this->paymentMethod = $paymentMethod;
        $this->triggerEvents = $triggerEvents;
        parent::__construct($context, $registry, $formFactory, $systemStore, $yesno, $customerGroup, $data);
    }

    /**
     * @inheritdoc
     */
    //@codingStandardsIgnoreLine
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $form = $this->getForm();
        $fieldset = $form->getElement('base_fieldset');
        $model = $this->_coreRegistry->registry('current_rule');

        $fieldset->addField(
            'item_qty',
            'text',
            [
                'name' => 'item_qty',
                'label' => __('Item QTY'),
                'title' => __('Item QTY'),
                'required' => false,
                'disabled' => false,
                'value' => 2,
                'note' => __(
                    'Show this bar if some item added to cart quantity is less or equal this number. 0 - always'
                ),
            ]
        );

        $fieldset->addField(
            'delay_time',
            'text',
            [
                'name' => 'delay_time',
                'label' => __('Delay all Triggers'),
                'title' => __('Delay all Triggers'),
                'required' => false,
                'disabled' => false,
                'value' => '0',
                'note' => __(
                    'How long to delay all triggers since last cart update. (in hours). 0 - no delay, 0.5 - 30 min'
                ),
            ]
        );

        $fieldset->addField(
            'show_after',
            'text',
            [
                'name' => 'show_after',
                'label' => __('Show after x Seconds'),
                'title' => __('Show after x Seconds'),
                'required' => false,
                'disabled' => false,
                'value' => 0,
                'note' => __('Show bar after X seconds'),
            ]
        );

        $fieldset->addField(
            'hide_after',
            'text',
            [
                'name' => 'hide_after',
                'label' => __('Hide after X Seconds'),
                'title' => __('Hide after X Seconds'),
                'required' => false,
                'disabled' => false,
                'value' => 5,
                'note' => __('0 - disabled auto hide'),
            ]
        );

        $fieldset->addField(
            'trigger_events',
            'multiselect',
            [
                'name' => 'trigger_events[]',
                'label' => __('Trigger on Event'),
                'title' => __('Trigger on Event'),
                'required' => true,
                'values' => $this->triggerEvents->toOptionArray(),
                'value' => \Magetrend\AbandonedCart\Model\Config\Source\Bar\Event::EVENT_TIME_OUT,
            ]
        );

        if ($model->getId()) {
            $form->setValues($model->getData());
        }
        $this->setForm($form);
        return $this;
    }
}
