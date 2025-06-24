<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote\Move;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Model\Quote as AmastyQuote;
use Magento\Framework\DataObjectFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository as MagentoQuoteRepository;

/**
 * @api
 */
class MergeQuotes
{
    /**
     * @var MagentoQuoteRepository
     */
    private $magentoQuoteRepository;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    public function __construct(MagentoQuoteRepository $magentoQuoteRepository, DataObjectFactory $dataObjectFactory)
    {
        $this->magentoQuoteRepository = $magentoQuoteRepository;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Merge items from Amasty Quote into Magento Quote.
     * Mark amasty quote items with options.
     *
     * @param CartInterface|Quote $cartQuote
     * @param QuoteInterface|AmastyQuote $approvedQuote
     */
    public function execute(CartInterface $cartQuote, QuoteInterface $approvedQuote): void
    {
        foreach ($approvedQuote->getAllItems() as $quoteItem) {
            if ($quoteItem->getOptionByCode('amasty_quote_price')) {
                $this->addOption($quoteItem, 'amasty_quote_id', (string) $approvedQuote->getId());
                $this->addOption($quoteItem, 'amasty_quote_item_id', (string) $quoteItem->getId());
            }
        }
        $cartQuote->merge($approvedQuote);

        if ($approvedQuote->getData(QuoteInterface::SHIPPING_CONFIGURE)) {
            foreach ($cartQuote->getAllAddresses() as $address) {
                $address->isDeleted(true);
            }
            foreach ($approvedQuote->getAllAddresses() as $address) {
                $newAddress = clone $address;
                $newAddress->setId(null);
                $cartQuote->addAddress($newAddress);
                foreach ($address->getAllShippingRates() as $shippingRate) {
                    $newShippingRate = clone $shippingRate;
                    $newShippingRate->setId(null);
                    $newAddress->addShippingRate($newShippingRate);
                }
            }
        }
    }

    private function addOption(CartItemInterface $cartItem, string $code, string $value): void
    {
        $quoteItemOption = $this->dataObjectFactory->create(['data' => [
            'code' => $code,
            'value' => $value,
            'product' => $cartItem->getProduct()
        ]]);
        $cartItem->addOption($quoteItemOption);
    }
}
