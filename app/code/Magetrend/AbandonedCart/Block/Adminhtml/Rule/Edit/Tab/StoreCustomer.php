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

namespace Magetrend\AbandonedCart\Block\Adminhtml\Rule\Edit\Tab;

use \Magento\Backend\Block\Widget\Form\Generic;
use \Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Bckend Rule Edit Store & Customer Group Tab Block
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class StoreCustomer extends Generic implements TabInterface
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
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->yesNo = $yesno;
        $this->customerGroup = $customerGroup;
        $this->paymentMethod = $paymentMethod;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    //@codingStandardsIgnoreLine
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_rule');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Stores & Customer Groups')]);

        if (!$this->_storeManager->isSingleStoreMode()) {
            $fieldset->addField(
                'store_ids',
                'multiselect',
                [
                    'name' => 'store_ids[]',
                    'label' => __('Apply in Stores'),
                    'title' => __('Apply in Stores'),
                    'required' => true,
                    'values' => $this->systemStore->getStoreValuesForForm(false, true),
                    'value' => 0,
                ]
            );
        }

        $fieldset->addField(
            'customer_groups',
            'multiselect',
            [
                'name' => 'customer_groups[]',
                'label' => __('Apply for Customer Groups'),
                'title' => __('Apply for Customer Groups'),
                'required' => true,
                'values' => $this->customerGroup->toOptionArray(),
                'value' => -1,
            ]
        );

        if ($model->getId()) {
            $form->setValues($model->getData());
        }
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Stores & Customer Groups');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Stores & Customer Groups');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
