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

namespace Magetrend\AbandonedCart\Block\Adminhtml\Rule\Edit\Tab\Schedule;

use Magento\Backend\Block\Widget;
use Magento\Catalog\Model\Product;

/**
 * Backend schedules fields widget class
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Field extends Widget
{
    /**
     * @var Product
     */
    public $productInstance;

    /**
     * @var \Magento\Framework\Object[]
     */
    public $values;

    /**
     * @var int
     */
    public $itemCount = 1;

    /**
     * Path to template file in theme.
     *
     * @var string
     */
    //@codingStandardsIgnoreLine
    protected $_template = 'rule/edit/tab/schedule/field.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry = null;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    public $configYesNo;

    /**
     * @var \Magetrend\AbandonedCart\Model\Config\Source\EmailTemplate
     */
    public $emailTemplateSource;

    /**
     * @var \Magetrend\AbandonedCart\Model\Config\Source\Schedule\Hour
     */
    public $hoursSource;

    /**
     * @var \Magetrend\AbandonedCart\Model\Config\Source\Schedule\Minute
     */
    public $minutesSource;

    /**
     * @var \Magetrend\AbandonedCart\Model\Config\Source\Schedule\Rule
     */
    public $ruleSource;

    /**
     * Field constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Config\Model\Config\Source\Yesno $configYesNo
     * @param \Magetrend\AbandonedCart\Model\Config\Source\Schedule\Template $emailTemplate
     * @param \Magetrend\AbandonedCart\Model\Config\Source\Schedule\Hour $hour
     * @param \Magetrend\AbandonedCart\Model\Config\Source\Schedule\Minute $minute
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Config\Model\Config\Source\Yesno $configYesNo,
        \Magetrend\AbandonedCart\Model\Config\Source\Schedule\Template $emailTemplate,
        \Magetrend\AbandonedCart\Model\Config\Source\Schedule\Hour $hour,
        \Magetrend\AbandonedCart\Model\Config\Source\Schedule\Minute $minute,
        \Magetrend\AbandonedCart\Model\Config\Source\Schedule\Rule $rule,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->emailTemplateSource = $emailTemplate;
        $this->configYesNo = $configYesNo;
        $this->coreRegistry = $registry;
        $this->hoursSource = $hour;
        $this->minutesSource = $minute;
        $this->ruleSource = $rule;
        parent::__construct($context, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    //@codingStandardsIgnoreLine
    protected function _construct()
    {
        parent::_construct();

        $this->setCanReadPrice(true);
        $this->setCanEditPrice(true);
    }

    /**
     * @return int
     */
    public function getItemCount()
    {
        return $this->itemCount;
    }

    /**
     * @param int $itemCount
     * @return $this
     */
    public function setItemCount($itemCount)
    {
        $this->itemCount = max($this->itemCount, $itemCount);
        return $this;
    }

    /**
     * Get Product
     *
     * @return Product
     */
    public function getRule()
    {
        return $this->coreRegistry->registry('current_rule');
    }

    /**
     * @param Product $product
     * @return $this
     */
    public function setProduct($product)
    {
        $this->productInstance = $product;
        return $this;
    }

    /**
     * Retrieve options field name prefix
     *
     * @return string
     */
    public function getFieldName()
    {
        return 'field[options]';
    }

    /**
     * Retrieve options field id prefix
     *
     * @return string
     */
    public function getFieldId()
    {
        return 'field_option';
    }

    /**
     * Check block is readonly
     *
     * @return bool
     */
    public function isReadonly()
    {
        return false;
    }

    /**
     * @return mixed
     */
    public function getAddButtonId()
    {
        $buttonId = $this->getLayout()->getBlock('admin.product.options')->getChildBlock('add_button')->getId();
        return $buttonId;
    }

    /**
     * @return mixed
     */
    public function getEmailTemplateSelectHtml()
    {

        $contentType = $this->_request->getParam('content_type');
        if (empty($contentType)) {
            $model = $this->coreRegistry->registry('current_rule');
            $contentType = $model->getType();
        }

        if ($contentType == \Magetrend\AbandonedCart\Model\Rule::TYPE_ABANDONED_CART) {
            $this->emailTemplateSource->setDefaultTemplate('magetrend_abandoned_cart_email');
        } elseif ($contentType == \Magetrend\AbandonedCart\Model\Rule::TYPE_FOLLOW_UP) {
            $this->emailTemplateSource->setDefaultTemplate('magetrend_follow_up_email');
        }

        $select = $this->getLayout()->createBlock(
            'Magento\Framework\View\Element\Html\Select'
        )->setData(
            [
                'id' => $this->getFieldId() . '_<%- data.id %>_email_template',
                'class' => 'select select-product-option-type required-option-select',
            ]
        )->setName(
            $this->getFieldName() . '[<%- data.id %>][email_template]'
        )->setOptions(
            $this->emailTemplateSource->toOptionArray()
        );
        return $select->getHtml();
    }

    /**
     * @return mixed
     */
    public function getHoursSelectHtml()
    {
        $select = $this->getLayout()->createBlock(
            'Magento\Framework\View\Element\Html\Select'
        )->setData(
            [
                'id' => $this->getFieldId() . '_<%- data.id %>_hour',
                'class' => 'select select-product-option-hour required-option-select change-title',
            ]
        )->setName(
            $this->getFieldName() . '[<%- data.id %>][delay_hour]'
        )->setOptions(
            $this->hoursSource->toOptionArray()
        );

        return $select->getHtml();
    }

    /**
     * @return mixed
     */
    public function getMinutesSelectHtml()
    {
        $select = $this->getLayout()->createBlock(
            'Magento\Framework\View\Element\Html\Select'
        )->setData(
            [
                'id' => $this->getFieldId() . '_<%- data.id %>_minute',
                'class' => 'select select-product-option-type required-option-select change-title',
            ]
        )->setName(
            $this->getFieldName() . '[<%- data.id %>][delay_minute]'
        )->setOptions(
            $this->minutesSource->toOptionArray()
        );

        return $select->getHtml();
    }

    /**
     * @return mixed
     */
    public function getCartPriceRuleSelectHtml()
    {
        $select = $this->getLayout()->createBlock(
            'Magento\Framework\View\Element\Html\Select'
        )->setData(
            [
                'id' => $this->getFieldId() . '_<%- data.id %>_discount',
                'class' => 'select select-product-option-type required-option-select',
            ]
        )->setName(
            $this->getFieldName() . '[<%- data.id %>][sales_rule_id]'
        )->setOptions(
            $this->ruleSource->toOptionArray()
        );

        return $select->getHtml();
    }

    /**
     * Retrieve html templates for different types of product custom options
     *
     * @return string
     */
    public function getTemplatesHtml()
    {
        $templates = $this->getChildHtml('text_option_type') . "\n";
        $templates.= $this->getChildHtml('select_option_type') . "\n";
        return $templates;
    }

    /**
     * @return \Magento\Framework\Object[]
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getOptionValues()
    {
        if (!$this->values) {
            $this->values = [];
            $fieldData = $this->getRule()->getSchedules();
            if (!empty($fieldData)) {
                foreach ($fieldData as $key => $field) {
                    //@codingStandardsIgnoreStart
                    $fData = $field->getData();
                    $fData['id'] = $fData['entity_id'];
                    $this->values[] = new \Magento\Framework\DataObject($fData);
                    //@codingStandardsIgnoreEnd
                }
            }
        }

        return $this->values;
    }

    /**
     * Retrieve html of scope checkbox
     *
     * @param string $id
     * @param string $name
     * @param boolean $checked
     * @param string $select_id
     * @param array $containers
     * @return string
     */
    public function getCheckboxScopeHtml($id, $name, $checked = true, $select_id = '-1', array $containers = [])
    {
        $checkedHtml = '';
        if ($checked) {
            $checkedHtml = ' checked="checked"';
        }
        $selectNameHtml = '';
        $selectIdHtml = '';
        if ($select_id != '-1') {
            $selectNameHtml = '[values][' . $select_id . ']';
            $selectIdHtml = 'select_' . $select_id . '_';
        }
        $containers[] = '$(this).up(1)';
        $containers = implode(',', $containers);
        $localId = $this->getFieldId() . '_' . $id . '_' . $selectIdHtml . $name . '_use_default';
        $localName = "options_use_default[" . $id . "]" . $selectNameHtml . "[" . $name . "]";
        $useDefault =
            '<div class="field-service">'
            . '<input type="checkbox" class="use-default-control"'
            . ' name="' . $localName . '"' . 'id="' . $localId . '"'
            . ' value="" '
            . $checkedHtml
            . ' onchange="toggleSeveralValueElements(this, [' . $containers . ']);" '
            . ' />'
            . '<label for="' . $localId . '" class="use-default">'
            . '<span class="use-default-label">' . __('Use Default') . '</span></label></div>';

        return $useDefault;
    }

    /**
     * @param float $value
     * @param string $type
     * @return float
     */
    public function getPriceValue($value, $type)
    {
        if ($type == 'percent') {
            return number_format($value, 2, null, '');
        } elseif ($type == 'fixed') {
            return number_format($value, 2, null, '');
        }

        return $value;
    }

    /**
     * Return product grid url for custom options import rule
     *
     * @return string
     */
    public function getProductGridUrl()
    {
        return $this->getUrl('catalog/*/optionsImportGrid');
    }

    /**
     * Return custom options getter URL for ajax queries
     *
     * @return string
     */
    public function getCustomOptionsUrl()
    {
        return $this->getUrl('catalog/*/customOptions');
    }
}
