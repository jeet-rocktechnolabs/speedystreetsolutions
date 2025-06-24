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

namespace Magetrend\AbandonedCart\Block\Adminhtml\Rule\Edit\Tab\Cart;

use \Magento\Backend\Block\Widget\Form\Generic;
use \Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Bckend Cart Rule Edit General Tab Block
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
     * @var \Magetrend\AbandonedCart\Model\Config\Source\Order\Event
     */
    public $cancelEvent;

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
        \Magetrend\AbandonedCart\Model\Config\Source\CancelEvent $cancelEvent,
        array $data = []
    ) {
        $this->cancelEvents = $cancelEvent;
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
            'cancel_events',
            'multiselect',
            [
                'name' => 'cancel_events[]',
                'label' => __('Cancel if'),
                'title' => __('Cancel if'),
                'required' => true,
                'values' => $this->cancelEvents->toOptionArray(),
                'value' => '',
            ]
        );

        if ($model->getId()) {
            $form->setValues($model->getData());
        }
        $this->setForm($form);
        return $this;
    }
}
