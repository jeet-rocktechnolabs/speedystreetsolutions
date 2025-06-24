<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote\Backend\Edit;

use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Amasty\RequestQuote\Model\Quote;
use Magento\Customer\Api\AddressRepositoryInterface as CustomerAddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote as MagentoQuote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\QuoteFactory as MagentoQuoteFactory;
use Magento\Quote\Model\QuoteRepository as MagentoQuoteRepository;
use Psr\Log\LoggerInterface;

class QuoteManagement
{
    public const AMASTY_QUOTE_FLAG = 'amasty_quote_flag';

    /**
     * @var array
     */
    private $customerFields = [
        'customer_id',
        'customer_tax_class_id',
        'customer_group_id',
        'customer_email',
        'customer_prefix',
        'customer_firstname',
        'customer_middlename',
        'customer_lastname',
        'customer_suffix',
        'customer_dob',
        'customer_note',
        'customer_note_notify',
        'customer_is_guest'
    ];

    /**
     * @var MagentoQuoteFactory
     */
    private $magentoQuoteFactory;

    /**
     * @var MagentoQuoteRepository
     */
    private $magentoQuoteRepository;

    /**
     * @var QuoteRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var CustomerAddressRepositoryInterface
     */
    private $customerAddressRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        CustomerAddressRepositoryInterface $customerAddressRepository,
        QuoteRepositoryInterface $quoteRepository,
        MagentoQuoteFactory $magentoQuoteFactory,
        MagentoQuoteRepository $magentoQuoteRepository,
        LoggerInterface $logger
    ) {
        $this->magentoQuoteFactory = $magentoQuoteFactory;
        $this->magentoQuoteRepository = $magentoQuoteRepository;
        $this->quoteRepository = $quoteRepository;
        $this->customerAddressRepository = $customerAddressRepository;
        $this->logger = $logger;
    }

    /**
     * Used for generate duplicate quote for edit actions
     *
     * @param Quote|MagentoQuote $quote
     * @return MagentoQuote
     */
    public function initFromQuote($quote): MagentoQuote
    {
        $newQuote = $this->magentoQuoteFactory->create();

        $this->copyItems($quote, $newQuote);

        $this->copyAddresses($quote, $newQuote, true);

        $newQuote->setStoreId($quote->getStoreId())
            ->setRemarks($quote->getRemarks());

        foreach ($this->customerFields as $field) {
            $value = $quote->getData($field);
            $newQuote->setData($field, $value);
        }

        $newQuote->setQuoteCurrencyCode($quote->getQuoteCurrencyCode());
        $newQuote->setForcedCurrency($quote->getQuoteCurrency());
        $newQuote->collectTotals();
        $newQuote->setIsActive(false);
        $this->saveMagentoQuote($newQuote);

        return $newQuote;
    }

    /**
     * @param MagentoQuote $quote
     */
    public function saveMagentoQuote(MagentoQuote $quote)
    {
        $quote->setData(self::AMASTY_QUOTE_FLAG, true);
        $this->magentoQuoteRepository->save($quote);
    }

    /**
     * @param Quote|MagentoQuote $quote
     * @return Quote
     */
    public function saveQuoteWithCustomerAddress($quote)
    {
        $quote->collectTotals();
        $this->quoteRepository->save($quote);
        $this->saveCustomerAddresses($quote);

        return $quote;
    }

    /**
     * @param Quote|MagentoQuote $quote
     * @param Quote|MagentoQuote $parentQuote
     * @return Quote
     */
    public function saveParentQuote($quote, $parentQuote)
    {
        $this->copyItems($quote, $parentQuote);
        $this->copyAddresses($quote, $parentQuote);

        $this->quoteRepository->save($parentQuote->collectTotals());

        return $parentQuote;
    }

    /**
     * Used for new created quote
     *
     * @param Quote|MagentoQuote $quote
     */
    private function saveCustomerAddresses($quote)
    {
        $customer = $quote->getCustomer();
        if ($customer->getId()) {
            foreach ($quote->getAllAddresses() as $address) {
                if (!$address->getSameAsBilling() && $address->getSaveInAddressBook()) {
                    try {
                        $customerAddress = $address->exportCustomerAddress();
                        $customerAddress->setCustomerId($customer->getId());
                        $this->updateCustomerAddress($customer, $customerAddress, $address->getAddressType());
                        $this->customerAddressRepository->save($customerAddress);
                        $quote->addCustomerAddress($customerAddress);
                        $address->setCustomerAddressData($customerAddress);
                        $address->setCustomerAddressId(null);
                    } catch (LocalizedException $e) {
                        $this->logger->error($e->getMessage());
                        continue;
                    }
                }
            }
        }
    }

    /**
     * @param CustomerInterface $customer
     * @param AddressInterface $customerAddress
     * @param string $addressType
     */
    private function updateCustomerAddress(
        CustomerInterface $customer,
        AddressInterface $customerAddress,
        string $addressType
    ) {
        switch ($addressType) {
            case Address::ADDRESS_TYPE_BILLING:
                if ($customer->getDefaultBilling() === null) {
                    $customerAddress->setIsDefaultBilling(true);
                }
                break;
            case Address::ADDRESS_TYPE_SHIPPING:
                if ($customer->getDefaultShipping() === null) {
                    $customerAddress->setIsDefaultShipping(true);
                }
                break;
        }
    }

    /**
     * @param Quote|MagentoQuote $fromQuote
     * @param Quote|MagentoQuote $toQuote
     */
    private function copyItems($fromQuote, $toQuote)
    {
        $toQuote->removeAllItems();

        foreach ($fromQuote->getAllVisibleItems() as $item) {
            try {
                $newItem = clone $item;
                $toQuote->addItem($newItem);
                if ($item->getHasChildren()) {
                    foreach ($item->getChildren() as $child) {
                        $newChild = clone $child;
                        $newChild->setParentItem($newItem);
                        $toQuote->addItem($newChild);
                    }
                }
            } catch (LocalizedException $e) {
                continue;
            }
        }
    }

    /**
     * @param Quote|MagentoQuote $fromQuote
     * @param Quote|MagentoQuote $toQuote
     * @param bool $clearCustomerAddressRelation
     */
    private function copyAddresses($fromQuote, $toQuote, $clearCustomerAddressRelation = false)
    {
        foreach ($toQuote->getAllAddresses() as $address) {
            $address->isDeleted(true);
        }
        foreach ($fromQuote->getAllAddresses() as $address) {
            $newAddress = clone $address;
            $newAddress->setId(null);
            if ($clearCustomerAddressRelation) {
                // set null because on edit we must modify quote address instead of customer address
                $newAddress->setCustomerAddressId(null);
            }
            foreach ($address->getShippingRatesCollection() as $shippingRate) {
                $newShippingRate = clone $shippingRate;
                $newShippingRate->setId(null);
                $newAddress->addShippingRate($newShippingRate);
            }
            $toQuote->addAddress($newAddress);
        }
    }
}
