<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Adminhtml\Quote\Create\Form;

use Amasty\RequestQuote\Model\Customer\ViewModel\AddressFormatter as AmastyAddressDataFormatter;
use Amasty\RequestQuote\Model\Customer\ViewModel\AddressFormatterFactory;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\ResourceModel\Address\Collection as AddressCollection;
use Magento\Eav\Model\AttributeDataFactory;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\ViewModel\Customer\AddressFormatter as MagentoAddressDataFormatter;

class Address extends AbstractForm
{
    /**
     * @var AmastyAddressDataFormatter|MagentoAddressDataFormatter\
     */
    private $addressFormatter;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @var \Magento\Customer\Model\Options
     */
    protected $options;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressService;

    /**
     * @var \Magento\Customer\Helper\Address
     */
    protected $_addressHelper;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Customer\Model\Address\Mapper
     */
    protected $addressMapper;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    private $countriesCollection;

    /**
     * @var \Amasty\RequestQuote\Model\Quote\Backend\Edit
     */
    private $quoteEdit;

    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    private $customerFormFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    private $countriesCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Amasty\RequestQuote\Model\Quote\Backend\Session $sessionQuote,
        \Amasty\RequestQuote\Model\Quote\Backend\Edit $quoteEdit,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Customer\Model\Options $options,
        \Magento\Customer\Model\Metadata\FormFactory $customerFormFactory,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Customer\Api\AddressRepositoryInterface $addressService,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countriesCollectionFactory,
        array $data = []
    ) {
        $this->quoteEdit = $quoteEdit;
        $this->customerFormFactory = $customerFormFactory;
        $this->options = $options;
        $this->directoryHelper = $directoryHelper;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->addressService = $addressService;
        $this->addressMapper = $addressMapper;
        $this->jsonEncoder = $jsonEncoder;
        $this->countriesCollectionFactory = $countriesCollectionFactory;
        parent::__construct(
            $context,
            $sessionQuote,
            $priceCurrency,
            $formFactory,
            $dataObjectProcessor,
            $data
        );
    }

    /**
     * Retrieve current customer address DATA collection.
     *
     * @return \Magento\Customer\Api\Data\AddressInterface[]
     */
    public function getAddressItems()
    {
        $items = [];
        if ($this->getCustomerId()) {
            $filter = $this->filterBuilder
                ->setField('parent_id')
                ->setValue($this->getCustomerId())
                ->setConditionType('eq')
                ->create();
            $this->searchCriteriaBuilder->addFilters([$filter]);
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $result = $this->addressService->getList($searchCriteria);
            $items = $result->getItems();
        }

        return $items;
    }

    /**
     * Return Customer Address Collection as JSON
     *
     * @return string
     */
    public function getAddressCollectionJson()
    {

        $data = [0 => $this->getEmptyAddressForm()->outputData(AttributeDataFactory::OUTPUT_FORMAT_JSON)];
        foreach ($this->getAddressItems() as $address) {
            $addressForm = $this->customerFormFactory->create(
                'customer_address',
                'adminhtml_customer_address',
                $this->addressMapper->toFlatArray($address),
                false,
                false
            );
            $data[$address->getId()] = $addressForm->outputData(
                AttributeDataFactory::OUTPUT_FORMAT_JSON
            );
        }

        return $this->jsonEncoder->encode($data);
    }

    /**
     * @return \Magento\Customer\Model\Metadata\Form
     */
    private function getEmptyAddressForm()
    {
        $defaultCountryId = $this->directoryHelper->getDefaultCountry($this->getStore());
        return $this->customerFormFactory->create(
            'customer_address',
            'adminhtml_customer_address',
            [AddressInterface::COUNTRY_ID => $defaultCountryId]
        );
    }

    /**
     * Prepare Form and add elements to form
     *
     * @return \Magento\Sales\Block\Adminhtml\Order\Create\Form\Address
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $storeId = $this->getQuoteEditModel()
            ->getSession()
            ->getStoreId();
        $this->_storeManager->setCurrentStore($storeId);

        $fieldset = $this->_form->addFieldset('main', ['no_container' => true]);

        $addressForm = $this->customerFormFactory->create('customer_address', 'adminhtml_customer_address');
        $attributes = $addressForm->getAttributes();
        $this->_addAttributesToForm($attributes, $fieldset);

        $this->updatePrefixElement($fieldset);
        $this->updateSuffixElement($fieldset);

        $this->updateRegion();

        $this->_form->setValues($this->getFormValues());

        $this->updateCountries();
        $this->updateVatElement();

        return $this;
    }

    /**
     * @param Fieldset $fieldset
     */
    private function updateSuffixElement($fieldset)
    {
        $suffixElement = $this->_form->getElement('suffix');
        if ($suffixElement) {
            $suffixOptions = $this->options->getNameSuffixOptions($this->getStore());
            if (!empty($suffixOptions)) {
                $fieldset->removeField($suffixElement->getId());
                $suffixField = $fieldset->addField(
                    $suffixElement->getId(),
                    'select',
                    $suffixElement->getData(),
                    $this->_form->getElement('lastname')->getId()
                );
                $suffixField->setValues($suffixOptions);
                if ($this->getAddressId()) {
                    $suffixField->addElementValues($this->getAddress()->getSuffix());
                }
            }
        }
    }

    /**
     * @param Fieldset $fieldset
     */
    private function updatePrefixElement($fieldset)
    {
        $prefixElement = $this->_form->getElement('prefix');
        if ($prefixElement) {
            $prefixOptions = $this->options->getNamePrefixOptions($this->getStore());
            if (!empty($prefixOptions)) {
                $fieldset->removeField($prefixElement->getId());
                $prefixField = $fieldset->addField($prefixElement->getId(), 'select', $prefixElement->getData(), '^');
                $prefixField->setValues($prefixOptions);
                if ($this->getAddressId()) {
                    $prefixField->addElementValues($this->getAddress()->getPrefix());
                }
            }
        }
    }

    private function updateVatElement()
    {
        // Set custom renderer for VAT field if needed
        $vatIdElement = $this->_form->getElement('vat_id');
        if ($vatIdElement && $this->getDisplayVatValidationButton() !== false) {
            $vatIdElement->setRenderer(
                $this->getLayout()->createBlock(
                    \Magento\Customer\Block\Adminhtml\Sales\Order\Address\Form\Renderer\Vat::class
                )->setJsVariablePrefix(
                    $this->getJsVariablePrefix()
                )->setData('amquote_block', true)
            );
        }
    }

    private function updateRegion()
    {
        $regionElement = $this->_form->getElement('region_id');
        if ($regionElement) {
            $regionElement->setNoDisplay(true);
        }
    }

    private function updateCountries()
    {
        $countryElement = $this->_form->getElement('country_id');

        $this->processCountryOptions($countryElement);

        if ($countryElement->getValue()) {
            $countryId = $countryElement->getValue();
            $countryElement->setValue(null);
            foreach ($countryElement->getValues() as $country) {
                if ($country['value'] == $countryId) {
                    $countryElement->setValue($countryId);
                }
            }
        }
        if ($countryElement->getValue() === null) {
            $countryElement->setValue(
                $this->directoryHelper->getDefaultCountry($this->getStore())
            );
        }
    }

    /**
     * Process country options.
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $countryElement
     * @return void
     */
    private function processCountryOptions(\Magento\Framework\Data\Form\Element\AbstractElement $countryElement)
    {
        $storeId = $this->getAddressStoreId();
        $options = $this->getCountriesCollection()
            ->loadByStore($storeId)
            ->toOptionArray();

        $countryElement->setValues($options);
    }

    /**
     * Retrieve Directory Countries collection
     *
     * @return \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    private function getCountriesCollection()
    {
        if (!$this->countriesCollection) {
            $this->countriesCollection = $this->countriesCollectionFactory->create();
        }

        return $this->countriesCollection;
    }

    /**
     * Add additional data to form element
     *
     * @param AbstractElement $element
     * @return $this
     */
    protected function _addAdditionalFormElementData(AbstractElement $element)
    {
        if ($element->getId() == 'region_id') {
            $element->setNoDisplay(true);
        }
        return $this;
    }

    /**
     * Return customer address id
     *
     * @return false
     */
    public function getAddressId()
    {
        return false;
    }

    /**
     * Return address store id.
     *
     * @return int
     */
    protected function getAddressStoreId()
    {
        return $this->_getSession()->getStoreId();
    }

    /**
     * @return \Amasty\RequestQuote\Model\Quote\Backend\Edit
     */
    public function getQuoteEditModel(): \Amasty\RequestQuote\Model\Quote\Backend\Edit
    {
        return $this->quoteEdit;
    }

    /**
     * @return bool
     */
    public function isSaveInAddressBook()
    {
        return $this->_data['save_in_address_book'] ?? false;
    }

    /**
     * @return array
     */
    public function getAddressArray(): array
    {
        if ($this->getCustomerId()) {
            /** @var AddressCollection $addressCollection */
            $addressCollection = $this->getData('customerAddressCollection');
            $addresses = $addressCollection->setCustomerFilter([$this->getCustomerId()])->toArray();
        } else {
            $addresses = [];
        }

        return $addresses;
    }

    /**
     * @return AmastyAddressDataFormatter|MagentoAddressDataFormatter
     */
    public function getCustomerAddressFormatter()
    {
        if ($this->addressFormatter === null) {
            $this->addressFormatter = $this->getData('customerAddressFormatterFactory')->create();
        }

        return $this->addressFormatter;
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getDontSaveInAddressBook(): bool
    {
        return (bool)$this->getAddressId();
    }
}
