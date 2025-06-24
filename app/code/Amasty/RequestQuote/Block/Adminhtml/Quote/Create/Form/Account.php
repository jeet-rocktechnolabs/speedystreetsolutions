<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Adminhtml\Quote\Create\Form;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Account extends AbstractForm
{
    /**
     * @var array
     */
    private $requiredAttributes = [
        'firstname',
        'middlename',
        'lastname'
    ];

    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    protected $_metadataFormFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $_extensibleDataObjectConverter;

    /**
     * @var CustomerMetadataInterface
     */
    private $customerMetadata;

    /**
     * @var \Amasty\RequestQuote\Helper\Data
     */
    private $helperData;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Amasty\RequestQuote\Model\Quote\Backend\Session $sessionQuote,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Customer\Model\Metadata\FormFactory $metadataFormFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        CustomerMetadataInterface $customerMetadata,
        \Amasty\RequestQuote\Helper\Data $helperData,
        array $data = []
    ) {
        $this->_metadataFormFactory = $metadataFormFactory;
        $this->customerRepository = $customerRepository;
        $this->_extensibleDataObjectConverter = $extensibleDataObjectConverter;
        parent::__construct(
            $context,
            $sessionQuote,
            $priceCurrency,
            $formFactory,
            $dataObjectProcessor,
            $data
        );
        $this->customerMetadata = $customerMetadata;
        $this->helperData = $helperData;
    }

    /**
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'head-account';
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Account Information');
    }

    /**
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Customer\Model\Metadata\Form $customerForm */
        $customerForm = $this->_metadataFormFactory->create('customer', 'adminhtml_checkout');

        // prepare customer attributes to show
        $attributes = [];

        ($this->helperData->getNamePrefixOptions() == '') ?: array_unshift($this->requiredAttributes, "prefix");
        ($this->helperData->getNameSuffixOptions() == '') ?: array_push($this->requiredAttributes, "suffix");

        // add system required attributes
        foreach ($customerForm->getSystemAttributes() as $attribute) {
            if ($attribute->isRequired()) {
                $attributes[$attribute->getAttributeCode()] = $attribute;
            }
        }

        if ($this->getQuote()->getCustomerIsGuest()) {
            unset($attributes['group_id']);
        }

        foreach ($this->requiredAttributes as $attributeCode) {
            if (!isset($attributes[$attributeCode])) {
                try {
                    $attributes[$attributeCode] = $this->customerMetadata->getAttributeMetadata($attributeCode);
                } catch (NoSuchEntityException $e) {
                    continue;
                }
            }
        }

        // add user defined attributes
        foreach ($customerForm->getUserAttributes() as $attribute) {
            $attributes[$attribute->getAttributeCode()] = $attribute;
        }

        $fieldset = $this->_form->addFieldset('main', []);

        $this->_addAttributesToForm($attributes, $fieldset);

        $this->_form->addFieldNameSuffix('quote[account]');

        $formValues = $this->getFormValues();
        $this->_form->setValues($formValues);

        return $this;
    }

    /**
     * @param AbstractElement $element
     * @return $this
     */
    protected function _addAdditionalFormElementData(AbstractElement $element)
    {
        switch ($element->getId()) {
            case 'email':
                $element->setRequired(1);
                $element->setClass('validate-email admin__control-text');
                break;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getFormValues()
    {
        try {
            $customer = $this->customerRepository->getById($this->getCustomerId());
            //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
        } catch (NoSuchEntityException $e) {
            // If customer does not exist do nothing.
        }
        $data = isset($customer)
            ? $this->_extensibleDataObjectConverter->toFlatArray(
                $customer,
                [],
                \Magento\Customer\Api\Data\CustomerInterface::class
            )
            : [];
        foreach ($this->getQuote()->getData() as $key => $value) {
            if (strpos($key, 'customer_') === 0) {
                $data[substr($key, 9)] = $value;
            }
        }

        if ($this->getQuote()->getCustomerEmail()) {
            $data['email'] = $this->getQuote()->getCustomerEmail();
        }

        return $data;
    }
}
