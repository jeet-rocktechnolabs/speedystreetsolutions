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
 * Bckend Rule Edit Coupon Tab Block
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Coupon extends Generic implements TabInterface
{
    /**
     * @var \Magetrend\AbandonedCart\Model\Config\Source\Format
     */
    public $codeFormat;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    public $yesNo;

    /**
     * @var \Magetrend\AbandonedCart\Model\Config\Source\Rule
     */
    public $priceRuleSource;

    /**
     * Coupon constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magetrend\AbandonedCart\Model\Config\Source\Format $format
     * @param \Magetrend\AbandonedCart\Model\Config\Source\Rule $rule
     * @param \Magento\Config\Model\Config\Source\Yesno $yesno
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magetrend\AbandonedCart\Model\Config\Source\Format $format,
        \Magento\Config\Model\Config\Source\Yesno $yesno,
        array $data = []
    ) {
        $this->codeFormat = $format;
        $this->yesNo = $yesno;
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

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Discount Code Settings')]);

        $fieldset->addField(
            'coupon_expire_in_days',
            'text',
            [
                'name' => 'coupon_expire_in_days',
                'label' => __('Expired in (days)'),
                'title' => __('Code Length'),
                'required' => false,
                'disabled' => false,
                'value' => 1
            ]
        );

        $fieldset->addField(
            'coupon_length',
            'text',
            [
                'name' => 'coupon_length',
                'label' => __('Code Length'),
                'title' => __('Code Length'),
                'required' => false,
                'disabled' => false,
                'value' => 8
            ]
        );

        $fieldset->addField(
            'coupon_format',
            'select',
            [
                'name' => 'coupon_format',
                'label' =>  __('Code Format'),
                'title' =>  __('Code Format'),
                'value' => 'alphanum',
                'options' => $this->codeFormat->toArray(),
            ]
        );

        $fieldset->addField(
            'coupon_prefix',
            'text',
            [
                'name' => 'coupon_prefix',
                'label' => __('Code Prefix'),
                'title' => __('Code Prefix'),
                'required' => false,
                'disabled' => false,
                'value' => 'NS-'
            ]
        );

        $fieldset->addField(
            'coupon_suffix',
            'text',
            [
                'name' => 'coupon_suffix',
                'label' => __('Code Suffix'),
                'title' => __('Code Suffix'),
                'required' => false,
                'disabled' => false,
            ]
        );

        $fieldset->addField(
            'coupon_dash',
            'text',
            [
                'name' => 'coupon_dash',
                'label' => __('Dash'),
                'title' => __('Dash'),
                'note' => __('Add Dash Every Time after X Symbols'),
                'required' => false,
                'disabled' => false,
                'value' => 4
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
        return __('Discount Coupon');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }
}
