<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote\Backend;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Model\Customer\Manager;
use Amasty\RequestQuote\Model\Quote\Backend\Carrier\Custom;
use Amasty\RequestQuote\Model\Quote\Backend\Edit\DateUtils;
use Amasty\RequestQuote\Model\Quote\Backend\Edit\ProductManagement;
use Amasty\RequestQuote\Model\Quote\Backend\Edit\QuoteManagement;
use Amasty\RequestQuote\Model\Source\Status;
use Exception;
use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Model\Metadata\Form as CustomerForm;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Quote\Model\Quote as MagentoQuote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Store\Model\StoreManagerInterface;

class Edit extends DataObject
{
    /**
     * @var \Amasty\RequestQuote\Model\Quote\Backend\Session
     */
    private $session;

    /**
     * @var \Amasty\RequestQuote\Model\Quote
     */
    private $quote;

    /**
     * @var boolean
     */
    private $needCollect;

    /**
     * @var boolean
     */
    private $needCollectCart = false;

    /**
     * @var boolean
     */
    private $isValidate = false;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    protected $metadataFormFactory;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var \Magento\Framework\DataObject\Factory
     */
    protected $objectFactory;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Customer\Model\Customer\Mapper
     */
    protected $customerMapper;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Sales\Model\AdminOrder\EmailSender
     */
    private $emailSender;

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var QuoteManagement
     */
    private $quoteManagement;

    /**
     * @var DateUtils
     */
    private $dateUtils;

    /**
     * @var JsonSerializer
     */
    private $jsonSerializer;

    /**
     * @var ProductManagement
     */
    private $productManagement;

    public function __construct(
        \Amasty\RequestQuote\Model\Quote\Backend\Session $quoteSession,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Metadata\FormFactory $metadataFormFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Amasty\RequestQuote\Api\QuoteRepositoryInterface $quoteRepository,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory,
        \Magento\Customer\Model\Customer\Mapper $customerMapper,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Amasty\RequestQuote\Model\Email\Sender $emailSender,
        Manager $manager,
        AddressFactory $addressFactory,
        StoreManagerInterface $storeManager,
        QuoteManagement $quoteManagement,
        DateUtils $dateUtils,
        JsonSerializer $jsonSerializer,
        ProductManagement $productManagement,
        array $data = []
    ) {
        parent::__construct($data);
        $this->session = $quoteSession;
        $this->logger = $logger;
        $this->messageManager = $messageManager;
        $this->customerRepository = $customerRepository;
        $this->metadataFormFactory = $metadataFormFactory;
        $this->customerFactory = $customerFactory;
        $this->groupRepository = $groupRepository;
        $this->emailSender = $emailSender;
        $this->quoteRepository = $quoteRepository;
        $this->accountManagement = $accountManagement;
        $this->customerMapper = $customerMapper;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->manager = $manager;
        $this->addressFactory = $addressFactory;
        $this->storeManager = $storeManager;
        $this->quoteManagement = $quoteManagement;
        $this->dateUtils = $dateUtils;
        $this->jsonSerializer = $jsonSerializer;
        $this->productManagement = $productManagement;
    }

    /**
     * @param boolean $flag
     * @return $this
     */
    public function setIsValidate($flag)
    {
        $this->isValidate = (bool)$flag;
        return $this;
    }

    /**
     * @return boolean
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsValidate()
    {
        return $this->isValidate;
    }

    /**
     * @param int|\Magento\Quote\Model\Quote\Item $item
     * @return \Magento\Quote\Model\Quote\Item|false
     */
    protected function getQuoteItem($item)
    {
        if ($item instanceof \Magento\Quote\Model\Quote\Item) {
            return $item;
        } elseif (is_numeric($item)) {
            return $this->getSession()->getQuote()->getItemById($item);
        }

        return false;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function setRecollect($flag)
    {
        $this->needCollect = $flag;
        return $this;
    }

    /**
     * @return $this
     */
    public function recollectQuote()
    {
        if ($this->needCollectCart === true) {
            $this->getQuote()->collectTotals();
            $this->quoteRepository->save($this->getQuote());
        }
        $this->setRecollect(true);

        return $this;
    }

    /**
     * @return $this
     */
    public function saveQuote()
    {
        if (!$this->getQuote()->getId()) {
            return $this;
        }

        if ($this->needCollect) {
            $this->getQuote()->collectTotals();
        }

        $this->quoteManagement->saveMagentoQuote($this->getQuote());
        return $this;
    }

    /**
     * @return \Amasty\RequestQuote\Model\Quote|MagentoQuote|mixed
     */
    public function saveFromQuote()
    {
        $quote = $this->getQuote();
        $parentQuote = $this->getSession()->getParentQuote();
        if (!$parentQuote->getId()) {
            $this->updateQuoteData($quote);
            $result = $this->quoteManagement->saveQuoteWithCustomerAddress($quote);
        } else {
            $this->updateQuoteData($parentQuote);
            $result = $this->quoteManagement->saveParentQuote($quote, $parentQuote);

            $this->getSession()->setQuote($parentQuote);
            if ($parentQuote->getStatus() != Status::ADMIN_NEW) {
                $this->emailSender->sendQuoteEditEmail($parentQuote);
            }
        }

        return $result;
    }

    /**
     * @param $quote
     * @return mixed
     */
    protected function updateQuoteData($quote)
    {
        if ($discount = (float) $this->getData(QuoteInterface::DISCOUNT)) {
            $quote->setData(QuoteInterface::DISCOUNT, $discount);
            $this->setData(
                'note_discount',
                __('Additional Discount in amount of %1 was applied.', $discount . '%')
            );
        } else {
            $quote->setData(QuoteInterface::DISCOUNT, null);
        }
        if ($surcharge = (float) $this->getData(QuoteInterface::SURCHARGE)) {
            $quote->setData(QuoteInterface::SURCHARGE, $surcharge);
            $this->setData(
                'note_discount',
                __('Additional Surcharge in amount of %1 was applied.', $surcharge . '%')
            );
        } else {
            $quote->setData(QuoteInterface::SURCHARGE, null);
        }

        $remarks = [];
        if ($quote->getRemarks()) {
            $remarks = $this->jsonSerializer->unserialize($quote->getRemarks());
        }

        if ($this->getData('note')) {
            $remarks['admin_note_remark'] = $this->getData('note');
        }

        if ($this->getData('note_discount')) {
            $remarks['admin_note'] = $this->getData('note_discount');
        }

        $quote->setRemarks($this->jsonSerializer->serialize($remarks));

        if ($expiredDate = $this->dateUtils->getExpiredDate(
            $quote->getData(QuoteInterface::EXPIRED_DATE),
            $this->getData(QuoteInterface::EXPIRED_DATE)
        )) {
            $quote->setExpiredDate($expiredDate);
        }
        if ($reminderDate = $this->dateUtils->getReminderDate(
            $quote->getData(QuoteInterface::REMINDER_DATE),
            $this->getData(QuoteInterface::REMINDER_DATE)
        )) {
            $quote->setReminderDate($reminderDate);
        }

        $quote->setData(
            QuoteInterface::SHIPPING_CAN_BE_MODIFIED,
            (bool) $this->getData(QuoteInterface::SHIPPING_CAN_BE_MODIFIED)
        );

        $quote->setData(
            QuoteInterface::SHIPPING_CONFIGURE,
            (bool) $this->getData(QuoteInterface::SHIPPING_CONFIGURE)
        );

        $quote->setData(
            QuoteInterface::CUSTOM_METHOD_ENABLED,
            (bool) $this->getData(QuoteInterface::CUSTOM_METHOD_ENABLED)
        );

        if ($this->hasData(QuoteInterface::CUSTOM_FEE) && $quote->getData(QuoteInterface::CUSTOM_METHOD_ENABLED)) {
            if ($customRate = $this->getCustomShippingRate()) {
                $quote->setData(QuoteInterface::CUSTOM_FEE, $customRate->getPrice());
            }
        }

        return $this;
    }

    /**
     * @return mixed|null
     */
    private function getCustomShippingRate()
    {
        $result = null;
        foreach ($this->getQuote()->getShippingAddress()->getAllShippingRates() as $shippingRate) {
            if ($shippingRate->getCode() === sprintf('%1$s_%1$s', Custom::CODE)) {
                $result = $shippingRate;
            }
        }

        return $result;
    }

    /**
     * @return \Amasty\RequestQuote\Model\Quote\Backend\Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @return MagentoQuote
     */
    public function getQuote()
    {
        if (!$this->quote) {
            $this->quote = $this->getSession()->getQuote(true);
        }

        return $this->quote;
    }

    /**
     * @param MagentoQuote $quote
     * @return $this
     */
    public function setQuote(MagentoQuote $quote)
    {
        $this->quote = $quote;
        return $this;
    }

    /**
     * @return int
     */
    public function getCustomerGroupId()
    {
        $groupId = $this->getQuote()->getCustomerGroupId();
        if (!$groupId) {
            $groupId = $this->getSession()->getCustomerGroupId();
        }

        return $groupId;
    }

    /**
     * @param int $itemId
     * @return $this
     */
    public function removeItem($itemId)
    {
        $this->removeQuoteItem($itemId);
        return $this;
    }

    /**
     * @param int $item
     * @return $this
     */
    public function removeQuoteItem($item)
    {
        $this->getQuote()->removeItem($item);
        $this->setRecollect(true);

        return $this;
    }

    /**
     * @param int|\Magento\Catalog\Model\Product $product
     * @param array|float|int|DataObject $config
     * @return $this
     * @throws LocalizedException
     */
    public function addProduct($product, $config = 1)
    {
        $this->productManagement->setQuote($this->getQuote());
        if ($this->productManagement->addProduct(
            $product,
            $this->getSession()->getStoreId(),
            $config
        )) {
            $this->setRecollect(true);

        }
        return $this;
    }

    /**
     * @param array $products
     * @return $this
     */
    public function addProducts(array $products)
    {
        foreach ($products as $productId => $config) {
            $config['qty'] = isset($config['qty']) ? (double)$config['qty'] : 1;
            try {
                $this->addProduct($productId, $config);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                return $e;
            }
        }

        return $this;
    }

    /**
     * @param array $items
     * @return $this
     * @throws Exception|LocalizedException
     */
    public function updateQuoteItems($items)
    {
        if (!is_array($items)) {
            return $this;
        }

        try {
            $this->productManagement->setQuote($this->getQuote());
            $this->productManagement->setResetPriceModificators((bool) $this->getResetPriceModificators());
            $this->productManagement->setDiscount($this->getData(QuoteInterface::DISCOUNT));
            $this->productManagement->setSurcharge($this->getData(QuoteInterface::SURCHARGE));
            $this->productManagement->updateQuoteItems($items);
        } catch (LocalizedException $e) {
            $this->recollectQuote();
            throw $e;
        } catch (Exception $e) {
            $this->logger->critical($e);
        }
        $this->recollectQuote();

        return $this;
    }

    /**
     * @return void
     */
    public function collectRates()
    {
        $this->getQuote()->collectTotals();
        return $this;
    }

    /**
     * Return Customer (Checkout) Form instance
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return CustomerForm
     */
    protected function _createCustomerForm(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        $customerForm = $this->metadataFormFactory->create(
            \Magento\Customer\Api\CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            'adminhtml_checkout',
            $this->customerMapper->toFlatArray($customer),
            false,
            CustomerForm::IGNORE_INVISIBLE
        );

        return $customerForm;
    }

    /**
     * Add account data to quote
     *
     * @param array $accountData
     * @return $this
     */
    public function setAccountData($accountData)
    {
        $customer = $this->getQuote()->getCustomer();
        if (empty($accountData['email'])) {
            $accountData['email'] = $customer->getEmail();
        }
        $form = $this->_createCustomerForm($customer);

        // emulate request
        $request = $form->prepareRequest($accountData);
        $data = $form->extractData($request);
        $data = $form->restoreData($data);
        $customer = $this->customerFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customer,
            $data,
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $this->getQuote()->updateCustomerData($customer);
        $data = [];

        $customerData = $this->customerMapper->toFlatArray($customer);
        foreach ($form->getAttributes() as $attribute) {
            $code = sprintf('customer_%s', $attribute->getAttributeCode());
            $data[$code] =  $customerData[$attribute->getAttributeCode()] ?? null;
        }

        if (isset($data['customer_group_id'])) {
            $customerGroup = $this->groupRepository->getById($data['customer_group_id']);
            $data['customer_tax_class_id'] = $customerGroup->getTaxClassId();
            $this->setRecollect(true);
        }

        $this->getQuote()->addData($data);

        return $this;
    }

    /**
     * @param   array $data
     * @return  $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function importPostData($data)
    {
        if (is_array($data)) {
            $this->addData($data);

            if (isset($data['account'])) {
                $this->setAccountData($data['account']);
            }

            if (isset($data['billing_address'])) {
                $this->setBillingAddress($data['billing_address']);
            }

            if (isset($data['shipping_address'])) {
                $this->setShippingAddress($data['shipping_address']);
            }

            if (isset($data['shipping_method'])) {
                $this->setShippingMethod($data['shipping_method']);
            }
        }

        return $this;
    }

    /**
     * Set shipping address into quote
     *
     * @param \Magento\Quote\Model\Quote\Address|array $address
     * @return $this
     */
    public function setShippingAddress($address)
    {
        if (is_array($address)) {
            $shippingAddress = $this->addressFactory->create()->setData(
                $address
            )->setAddressType(
                \Magento\Quote\Model\Quote\Address::TYPE_SHIPPING
            );
            if (!$this->getQuote()->isVirtual()) {
                $this->setQuoteAddress($shippingAddress, $address);
            }
            /**
             * save_in_address_book is not a valid attribute and is filtered out by _setQuoteAddress,
             * that is why it should be added after _setQuoteAddress call
             */
            $saveInAddressBook = (int)(!empty($address['save_in_address_book']));
            $shippingAddress->setData('save_in_address_book', $saveInAddressBook);
        }
        if ($address instanceof \Magento\Quote\Model\Quote\Address) {
            $shippingAddress = $address;
        }

        $this->setRecollect(true);
        $this->getQuote()->setShippingAddress($shippingAddress);

        return $this;
    }

    /**
     * Set billing address into quote
     *
     * @param array $address
     * @return $this
     */
    public function setBillingAddress($address)
    {
        if (!is_array($address)) {
            return $this;
        }

        $billingAddress = $this->addressFactory->create()
            ->setData($address)
            ->setAddressType(Address::TYPE_BILLING);

        $this->setQuoteAddress($billingAddress, $address);

        /**
         * save_in_address_book is not a valid attribute and is filtered out by _setQuoteAddress,
         * that is why it should be added after _setQuoteAddress call
         */
        $saveInAddressBook = (int)(!empty($address['save_in_address_book']));
        $billingAddress->setData('save_in_address_book', $saveInAddressBook);

        $quote = $this->getQuote();
        if (!$quote->isVirtual() && $this->getShippingAddress()->getSameAsBilling()) {
            $address['save_in_address_book'] = 0;
            $this->setShippingAddress($address);
        }

        // not assigned billing address should be saved as new
        // but if quote already has the billing address it won't be overridden
        if (empty($billingAddress->getCustomerAddressId())) {
            $billingAddress->setCustomerAddressId(null);
            $quote->getBillingAddress()->setCustomerAddressId(null);
        }
        $quote->setBillingAddress($billingAddress);

        return $this;
    }

    /**
     * Set and validate Quote address
     *
     * All errors added to _errors
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param array $data
     * @return $this
     */
    protected function setQuoteAddress(\Magento\Quote\Model\Quote\Address $address, array $data)
    {
        $isAjax = !$this->getIsValidate();

        // Region is a Data Object, so it is represented by an array. validateData() doesn't understand arrays, so we
        // need to merge region data with address data. This is going to be removed when we switch to use address Data
        // Object instead of the address model.
        // Note: if we use getRegion() here it will pull region from db using the region_id
        $data = isset($data['region']) && is_array($data['region']) ? array_merge($data, $data['region']) : $data;

        $addressForm = $this->metadataFormFactory->create(
            AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
            'adminhtml_customer_address',
            $data,
            $isAjax,
            CustomerForm::DONT_IGNORE_INVISIBLE,
            []
        );

        // prepare request
        // save original request structure for files
        if ($address->getAddressType() == \Magento\Quote\Model\Quote\Address::TYPE_SHIPPING) {
            $requestData = ['quote' => ['shipping_address' => $data]];
            $requestScope = 'quote/shipping_address';
        } else {
            $requestData = ['quote' => ['billing_address' => $data]];
            $requestScope = 'quote/billing_address';
        }
        $request = $addressForm->prepareRequest($requestData);
        $addressData = $addressForm->extractData($request, $requestScope);
        if ($this->getIsValidate()) {
            $errors = $addressForm->validateData($addressData);
            if ($errors !== true) {
                if ($address->getAddressType() == \Magento\Quote\Model\Quote\Address::TYPE_SHIPPING) {
                    $typeName = __('Shipping Address: ');
                } else {
                    $typeName = __('Billing Address: ');
                }
                foreach ($errors as $error) {
                    $this->_errors[] = $typeName . $error;
                }
                $address->setData($addressForm->restoreData($addressData));
            } else {
                $address->setData($addressForm->compactData($addressData));
            }
        } else {
            $address->addData($addressForm->restoreData($addressData));
        }

        return $this;
    }

    /**
     * Set shipping method
     *
     * @param string $method
     * @return $this
     */
    public function setShippingMethod($method)
    {
        $this->getShippingAddress()->setShippingMethod($method);
        $this->setRecollect(true);

        return $this;
    }

    /**
     * Collect shipping data for quote shipping address
     *
     * @return $this
     */
    public function collectShippingRates()
    {
        $store = $this->getQuote()->getStore();
        $this->storeManager->setCurrentStore($store);
        $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        $this->collectRates();

        return $this;
    }

    /**
     * @param \Magento\Store\Model\Store $store
     * @return bool
     */
    protected function customerIsInStore($store)
    {
        $customer = $this->getQuote()->getCustomer();

        return $customer->getWebsiteId() == $store->getWebsiteId()
            || $this->accountManagement->isCustomerInStore($customer->getWebsiteId(), $store->getId());
    }

    /**
     * @param $quote
     * @return MagentoQuote
     */
    public function initFromQuote($quote)
    {
        $newQuote = $this->quoteManagement->initFromQuote($quote);
        $this->getSession()->setQuoteId($newQuote->getId());
        $this->getSession()->setQuoteIdParrentId($newQuote->getId(), $quote->getId());

        return $newQuote;
    }

    /**
     * @return CustomerInterface|\Magento\Framework\Api\ExtensibleDataInterface
     * @throws LocalizedException
     */
    public function prepareCustomer()
    {
        if ($this->getQuote()->getCustomer()->getId() === null) {
            $customer = $this->manager->create($this->getData());
            $this->getQuote()->setCustomer($customer);
        } elseif ($this->getQuote()->getCustomer()->getWebsiteId() != $this->getQuote()->getStore()->getWebsiteId()) {
            try {
                $customer = $this->customerRepository->get(
                    $this->getQuote()->getCustomer()->getEmail(),
                    $this->getQuote()->getStore()->getWebsiteId()
                );
                $this->getQuote()->setCustomer($customer);
            } catch (NoSuchEntityException $e) {
                $oldAddresses = $this->getQuote()->getCustomer()->getAddresses() ?: [];
                $newAddresses = [];
                $customer = $this->getQuote()
                    ->getCustomer()
                    ->setId(null)
                    ->setAddresses(null)
                    ->setDefaultBilling(null)
                    ->setDefaultShipping(null)
                    ->setWebsiteId($this->getQuote()->getStore()->getWebsiteId())
                    ->setStoreId($this->getQuote()->getStoreId());
                $this->getQuote()->getBillingAddress()->setCustomerAddressId(null);
                $this->getQuote()->getShippingAddress()->setCustomerAddressId(null);
                foreach ($oldAddresses as $oldAddress) {
                    $newAddress = clone $oldAddress;
                    $newAddress->setId(null);
                    $newAddress->setCustomerId($customer->getId());
                    $newAddresses[] = $newAddress;
                }
                $customer->setAddresses($newAddresses);
            }
            foreach ($this->getQuote()->getAllAddresses() as $address) {
                $address->isDeleted(true);
            }
        }

        return $this->getQuote()->getCustomer();
    }

    /**
     * Set shipping address to be same as billing
     *
     * @param bool $flag If true - don't save in address book and actually copy data across billing and shipping
     *                   addresses
     * @return $this
     */
    public function setShippingAsBilling($flag)
    {
        if ($flag) {
            $tmpAddress = clone $this->getBillingAddress();
            $tmpAddress->unsAddressId()->unsAddressType();
            $data = $tmpAddress->getData();
            $data['save_in_address_book'] = 0;
            // Do not duplicate address (billing address will do saving too)
            $this->getShippingAddress()->addData($data);
        }
        $this->getShippingAddress()->setData('same_as_billing', $flag);
        $this->setRecollect(true);
        return $this;
    }

    /**
     * Retrieve quote billing address
     *
     * @return \Magento\Quote\Model\Quote\Address
     */
    public function getBillingAddress()
    {
        return $this->getQuote()->getBillingAddress();
    }

    /**
     * Retrieve order quote shipping address
     *
     * @return \Magento\Quote\Model\Quote\Address
     */
    public function getShippingAddress()
    {
        return $this->getQuote()->getShippingAddress();
    }
}
