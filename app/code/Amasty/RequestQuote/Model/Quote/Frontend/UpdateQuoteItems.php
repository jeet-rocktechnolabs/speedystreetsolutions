<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote\Frontend;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Api\Data\QuoteItemInterface;
use Amasty\RequestQuote\Helper\Cart as CartHelper;
use Amasty\RequestQuote\Helper\Data as Helper;
use Amasty\RequestQuote\Model\HidePrice\Provider as HidePriceProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filter\LocalizedToNormalized;
use Magento\Framework\Locale\ResolverInterface as LocaleResolver;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Store\Api\Data\StoreInterface;

/**
 * @api
 */
class UpdateQuoteItems
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var HidePriceProvider
     */
    private $hidePriceProvider;

    /**
     * @var CartHelper
     */
    private $cartHelper;

    /**
     * @var array
     */
    private $dataKeysMap;

    /**
     * @var LocalizedToNormalized
     */
    private $localizedToNormalized;

    public function __construct(
        LocaleResolver $localeResolver,
        PriceCurrencyInterface $priceCurrency,
        Helper $helper,
        HidePriceProvider $hidePriceProvider,
        CartHelper $cartHelper,
        array $dataKeysMap = [],
        LocalizedToNormalized $localizedToNormalized = null
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->helper = $helper;
        $this->hidePriceProvider = $hidePriceProvider;
        $this->cartHelper = $cartHelper;
        $this->dataKeysMap = $dataKeysMap;
        $this->localizedToNormalized = $localizedToNormalized
            ?? ObjectManager::getInstance()->get(LocalizedToNormalized::class);
        $this->setLocaleToFilter($localeResolver->getLocale());
    }

    /**
     * @param QuoteInterface $quote
     * @param array[] $cartData
     * @return QuoteItem[]
     * @throws NoSuchEntityException
     */
    public function execute(QuoteInterface $quote, array $cartData): array
    {
        $quoteItems = [];
        foreach ($cartData as $index => &$data) {
            if (isset($data[$this->getMappedItemDataKey('qty')])) {
                $cartData[$index][$this->getMappedItemDataKey('qty')] = $this->filter(
                    $data[$this->getMappedItemDataKey('qty')]
                );
            }

            $quoteItem = $data['quote_item'] ?? $quote->getItemById($index);
            $quoteItems[] = $quoteItem;
            if (!$quoteItem) {
                throw new NoSuchEntityException(__('Could not find quote item with id: %1.', $index));
            }

            if (isset($data[$this->getMappedItemDataKey('price')]) && $this->helper->isAllowCustomizePrice()) {
                $price = $this->convertPriceToBase(
                    (float) $this->filter($data[$this->getMappedItemDataKey('price')]),
                    $quote->getStore()
                );
            } else {
                $price = $quoteItem->getProduct()->getFinalPrice($data[$this->getMappedItemDataKey('qty')] ?? 1);
            }

            if (!$this->helper->isAllowCustomizePrice()
                && $this->hidePriceProvider->isHidePrice($quoteItem->getProduct())
            ) {
                $price = 0;
            }

            $customPriceFromRequest = $this->convertPriceToCurrent($price);

            if (isset($data[$this->getMappedItemDataKey('qty')])
                && !$this->hidePriceProvider->isHidePrice($quoteItem->getProduct())
            ) {
                $productFinalPrice = $quoteItem->getProduct()->getFinalPrice(
                    $this->filter($data[$this->getMappedItemDataKey('qty')])
                );
                $price = min($productFinalPrice, $price);
            }

            $quoteItem->setCustomPrice($price);
            $quoteItem->setOriginalCustomPrice($price);

            $additionalData = [
                QuoteItemInterface::CUSTOM_PRICE => $customPriceFromRequest,
                QuoteItemInterface::HIDE_ORIGINAL_PRICE => $this->hidePriceProvider->isHidePrice(
                    $quoteItem->getProduct()
                )
            ];
            if (isset($data[$this->getMappedItemDataKey('note')])) {
                $additionalData[QuoteItemInterface::CUSTOMER_NOTE_KEY] = $this->trim(
                    $data[$this->getMappedItemDataKey('note')]
                );
            }

            $quoteItem->setAdditionalData(
                $this->cartHelper->updateAdditionalData(
                    $quoteItem->getAdditionalData(),
                    $additionalData
                )
            );
        }

        return $quoteItems;
    }

    /**
     * @param string|float $value
     * @return string|float
     */
    private function filter($value)
    {
        if (is_string($value)) {
            return $this->localizedToNormalized->filter($this->trim($value));
        } else {
            return $value;
        }
    }

    /**
     * @param string|float $value
     * @return string|float
     */
    private function trim($value)
    {
        return is_string($value) ? trim($value) : $value;
    }

    private function convertPriceToBase(float $price, StoreInterface $currentStore): float
    {
        $rate = $currentStore->getBaseCurrency()->getRate(
            $this->priceCurrency->getCurrency($currentStore)
        );
        if ($rate != 1) {
            $price = $price / (float) $rate;
        }

        return $price;
    }

    private function convertPriceToCurrent(float $price): float
    {
        return $this->priceCurrency->convert($price);
    }

    private function getMappedItemDataKey(string $key): string
    {
        return $this->dataKeysMap[$key] ?? $key;
    }

    private function setLocaleToFilter(string $locale): void
    {
        $this->localizedToNormalized->setOptions(['locale' => $locale]);
    }
}
