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
 * Bckend Bar Rule Edit Settings Tab Block
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Settings extends Generic implements TabInterface
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

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Bar Settings')]);

        $fieldset->addField(
            'bar_text',
            'textarea',
            [
                'name' => 'bar_text',
                'label' => __('Message'),
                'title' => __('Message'),
                'required' => true,
                'disabled' => false,
                'value' => __('Some of your items you added to cart are almost sold out. Hurry up!'),
                'note' => __('HTML allowed'),
            ]
        );

        $fieldset->addField(
            'color_1',
            'text',
            [
                'name' => 'color_1',
                'label' => __('Background Color'),
                'title' => __('Background Color'),
                'required' => false,
                'disabled' => false,
                'class'  => 'jscolor {hash:true,refine:false}',
                'value' => '#edf058',
            ]
        );

        $fieldset->addField(
            'color_2',
            'text',
            [
                'name' => 'color_2',
                'label' => __('Text Font Color'),
                'title' => __('Text Font Color'),
                'required' => false,
                'disabled' => false,
                'class'  => 'jscolor {hash:true,refine:false}',
                'value' => '#000000'
            ]
        );

        $fieldset->addField(
            'font_size_1',
            'text',
            [
                'name' => 'font_size_1',
                'label' => __('Font Size'),
                'title' => __('Font Size'),
                'required' => false,
                'disabled' => false,
                'value' => '24px'
            ]
        );

        $fieldset->addField(
            'font_1',
            'text',
            [
                'name' => 'font_1',
                'label' => __('Font Family'),
                'title' => __('Font Family'),
                'required' => false,
                'disabled' => false,
                'value' => 'Arial'
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
