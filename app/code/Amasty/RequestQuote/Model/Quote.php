<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Model\Email\AdminNotification;
use Amasty\RequestQuote\Helper\Data;
use Amasty\RequestQuote\Model\Quote\AdvancedMergeResult;
use Amasty\RequestQuote\Model\Quote\AdvancedMergeResultFactory;
use Amasty\RequestQuote\Model\Source\Status;
use Magento\Directory\Model\Currency;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Quote extends \Magento\Quote\Model\Quote implements QuoteInterface
{
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
     * @var array
     */
    private $ignoreProductTypes = [
        'giftcard'
    ];

    /**
     * @var null|\Amasty\RequestQuote\Model\Source\Status
     */
    private $statusSource = null;

    /**
     * @var null|\Magento\Directory\Model\CurrencyFactory
     */
    private $currencyDirectoryFactory = null;

    /**
     * @var null|Currency
     */
    private $quoteCurrency = null;

    /**
     * @var null|Currency
     */
    private $baseCurrency = null;

    /**
     * @var null|ResolverInterface
     */
    private $localeResolver = null;

    /**
     * @var null|TimezoneInterface
     */
    private $timezone = null;

    /**
     * @var null|AdvancedMergeResultFactory
     */
    private $advancedMergeResultFactory = null;

    /**
     * @var null|Data
     */
    private $helper = null;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Amasty\RequestQuote\Model\ResourceModel\Quote::class);
        $this->statusSource = $this->_data['status_source'] ?? null;
        $this->currencyDirectoryFactory = $this->_data['currency_factory'] ?? null;
        $this->localeResolver = $this->_data['locale_resolver'] ?? null;
        $this->timezone = $this->_data['timezone'] ?? null;
        $this->advancedMergeResultFactory = $this->_data['advancedMergeResultFactory'] ?? null;
        $this->helper = $this->_data['helper'] ?? null;
    }

    public function getStatus(): int
    {
        if (!$this->hasData(QuoteInterface::STATUS)) {
            $this->setData(QuoteInterface::STATUS, Status::CREATED);
            $this->setData(QuoteInterface::ADMIN_NOTIFICATION_SEND, AdminNotification::NOT_SENT);
        }

        return (int)$this->getData(QuoteInterface::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsActive()
    {
        return $this->getStatus() == Status::CREATED;
    }

    /**
     * @param int $itemId
     * @param \Magento\Framework\DataObject $buyRequest
     * @param null $params
     * @return \Magento\Quote\Model\Quote\Item
     */
    public function updateItem($itemId, $buyRequest, $params = null)
    {
        $resultItem =  parent::updateItem($itemId, $buyRequest, $params);
        if ($buyRequest->getData('price')) {
            $resultItem->setPrice($buyRequest->getData('price'));
        }
        if ($resultItem->hasCustomPrice()) {
            $resultItem->setPrice($resultItem->getCustomPrice());
        }
        return $resultItem;
    }

    /**
     * @return bool
     */
    public function canOrder()
    {
        return $this->getStatus() == Status::APPROVED;
    }

    /**
     * @return bool
     */
    public function canHold()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function canRenew()
    {
        return in_array($this->getStatus(), [Status::COMPLETE, Status::CANCELED]);
    }

    /**
     * @return bool
     */
    public function canApprove()
    {
        return in_array($this->getStatus(), [
            Status::PENDING,
            Status::ADMIN_CREATED
        ]);
    }

    /**
     * @return bool
     */
    public function canClose()
    {
        return !$this->canRenew() && $this->getStatus() != Status::EXPIRED;
    }

    /**
     * @return bool
     */
    public function canEdit()
    {
        return in_array($this->getStatus(), [
            Status::PENDING,
            Status::APPROVED,
            Status::ADMIN_CREATED
        ]) && !$this->getData('is_active');
    }

    public function setStatus(int $status): void
    {
        $this->setData(QuoteInterface::STATUS, $status);
    }

    /**
     * @param $submitedDate
     * @return $this
     */
    public function setSubmitedDate($submitedDate)
    {
        $this->setData(QuoteInterface::SUBMITED_DATE, $submitedDate);
        return $this;
    }

    /**
     * @return string
     */
    public function prepareIncrementId()
    {
        return sprintf("%s%'.09d", $this->getStoreId(), $this->getId());
    }

    /**
     * @return string
     */
    public function getStatusLabel()
    {
        return $this->statusSource->getStatusLabel($this->getStatus());
    }

    public function isShippingConfigure(): bool
    {
        return (bool) $this->getDataByKey(self::SHIPPING_CONFIGURE);
    }

    public function setShippingConfigure(bool $shippingConfigure): void
    {
        $this->setData(self::SHIPPING_CONFIGURE, $shippingConfigure);
    }

    /**
     * @return Currency
     */
    public function getQuoteCurrency()
    {
        if ($this->quoteCurrency === null) {
            $this->quoteCurrency = $this->currencyDirectoryFactory->create();
            $this->quoteCurrency->load($this->getQuoteCurrencyCode());
        }

        return $this->quoteCurrency;
    }

    /**
     * @return Currency
     */
    public function getBaseCurrency()
    {
        if ($this->baseCurrency === null) {
            $this->baseCurrency = $this->currencyDirectoryFactory->create();
            $this->baseCurrency->load($this->getBaseCurrencyCode());
        }

        return $this->baseCurrency;
    }

    /**
     * @param   float $price
     * @param   bool  $addBrackets
     * @return  string
     */
    public function formatPrice($price, $addBrackets = false)
    {
        return $this->formatPricePrecision($price, 2, $addBrackets);
    }

    /**
     * @param $price
     * @param bool $addBrackets
     * @return string
     */
    public function formatBasePrice($price, $addBrackets = false)
    {
        return $this->formatBasePricePrecision($price, 2, $addBrackets);
    }

    /**
     * @param float $price
     * @param int $precision
     * @param bool $addBrackets
     * @return string
     */
    public function formatPricePrecision($price, $precision, $addBrackets = false)
    {
        return $this->getQuoteCurrency()->formatPrecision($price, $precision, [], true, $addBrackets);
    }

    /**
     * @param float $price
     * @param int $precision
     * @param bool $addBrackets
     * @return string
     */
    public function formatBasePricePrecision($price, $precision, $addBrackets = false)
    {
        return $this->getBaseCurrency()->formatPrecision($price, $precision, [], true, $addBrackets);
    }

    /**
     * @inheritdoc
     */
    public function collectTotals()
    {
        if ($this->getTotalsCollectedFlag()) {
            return $this;
        }

        // address initialization before collecting totals
        $this->getBillingAddress();
        $this->getShippingAddress();

        return parent::collectTotals();
    }

    /**
     * @return string
     */
    public function prepareCustomerName()
    {
        return ($this->getCustomerPrefix() ? $this->getCustomerPrefix() . ' ' : '')
            . $this->getCustomerFirstname()
            . ($this->getCustomerMiddlename() ? ' ' . $this->getCustomerMiddlename() : '')
            . ' ' . $this->getCustomerLastname()
            . ($this->getCustomerSuffix() ? ' ' . $this->getCustomerSuffix() : '');
    }

    /**
     * @param int $quoteId
     * @return $this
     */
    public function loadMagentoQuoteByIdWithoutStore($quoteId)
    {
        $this->_getResource()->loadMagentoQuoteByIdWithoutStore($this, $quoteId);
        $this->_afterLoad();
        return $this;
    }

    public function getCreatedAtFormatted(int $format): string
    {
        return $this->formatDate($this->getCreatedAt(), $format);
    }

    public function getSubmitedDateFormatted(int $format): string
    {
        return $this->formatDate($this->getSubmitedDate(), $format);
    }

    private function formatDate(string $date, int $format): string
    {
        return $this->timezone->formatDateTime(
            new \DateTime($date),
            $format,
            $format,
            $this->localeResolver->getDefaultLocale(),
            $this->timezone->getConfigTimezone('store', $this->getStore())
        );
    }

    /**
     * @return bool
     */
    public function isCurrencyDifferent()
    {
        return $this->getQuoteCurrencyCode() != $this->getBaseCurrencyCode();
    }

    /**
     * @return string
     */
    public function getQuoteCurrencyCode()
    {
        return $this->getData('quote_currency_code');
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param bool $advancedMode
     * @param bool $inQuote
     */
    public function advancedMerge(\Magento\Quote\Model\Quote $quote, $advancedMode, $inQuote): AdvancedMergeResult
    {
        $warnings = [];
        $result = false;
        $itemsForRemove = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($inQuote) {
                if (in_array($item->getProductType(), $this->ignoreProductTypes)) {
                    $warnings[] = __('The Gift Card can not be converted from shopping cart to quote.');
                    continue;
                }
                $product = $this->productRepository->getById($item->getProductId(), true, $this->getStoreId());
                if ($product->getData(Data::ATTRIBUTE_NAME_HIDE_BUY_BUTTON) ||
                    !empty(array_uintersect(
                        $product->getCategoryIds(),
                        $this->helper->getExcludeCategories(),
                        'strcmp'
                    ))
                ) {
                    $warnings[] = __('One or several Products can not be converted from shopping cart to quote.');
                    continue;
                }
            }
            $result = true;
            $found = false;
            foreach ($this->getAllItems() as $quoteItem) {
                if ($quoteItem->compare($item)) {
                    $quoteItem->setQty($quoteItem->getQty() + $item->getQty());
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $newItem = clone $item;
                $this->addItem($newItem);
                if ($item->getHasChildren()) {
                    foreach ($item->getChildren() as $child) {
                        $newChild = clone $child;
                        $newChild->setParentItem($newItem);
                        $this->addItem($newChild);
                    }
                }
            }
            $itemsForRemove[] = $item->getItemId();
        }

        if ($result) {
            $this->setStoreId($quote->getStoreId());

            if ($advancedMode && count($this->getAllAddresses()) == 0) {
                foreach ($quote->getAllAddresses() as $address) {
                    $newAddress = clone $address;
                    $this->addAddress($newAddress);
                }

                foreach ($this->customerFields as $field) {
                    $value = $quote->getData($field);
                    $this->setData($field, $value);
                }
            }
            foreach ($itemsForRemove as $itemId) {
                $quote->removeItem($itemId);
            }
            $this->setTotalsCollectedFlag(false);
            $this->collectTotals();
        }

        return $this->advancedMergeResultFactory->create(['result' => $result, 'warnings' => $warnings]);
    }

    /**
     * @param   \Magento\Quote\Model\Quote\Item $item
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addItem(\Magento\Quote\Model\Quote\Item $item)
    {
        $item->setNoDiscount(true);
        return parent::addItem($item);
    }

    /**
     * @return $this
     */
    public function beforeSave()
    {
        $this->setForcedCurrency($this->getQuoteCurrency());
        return parent::beforeSave();
    }

    public function getCustomerFirstname(): ?string
    {
        return $this->_getData(self::CUSTOMER_FIRSTNAME);
    }

    public function getCustomerLastname(): ?string
    {
        return $this->_getData(self::CUSTOMER_LASTNAME);
    }

    public function getCustomerEmail(): ?string
    {
        return $this->_getData(self::CUSTOMER_EMAIL);
    }

    public function getQuoteCustomerNote(): ?string
    {
        return $this->getData(self::QUOTE_CUSTOMER_NOTE_KEY);
    }

    public function setQuoteCustomerNote(string $customerNote): void
    {
        $this->setData(self::QUOTE_CUSTOMER_NOTE_KEY, $customerNote);
    }
}
