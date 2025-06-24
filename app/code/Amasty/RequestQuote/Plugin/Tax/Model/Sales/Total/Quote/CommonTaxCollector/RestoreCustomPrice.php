<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Plugin\Tax\Model\Sales\Total\Quote\CommonTaxCollector;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Store\Model\Store;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;

class RestoreCustomPrice
{
    /**
     * @var QuoteResource
     */
    private $quoteResource;

    public function __construct(QuoteResource $quoteResource)
    {
        $this->quoteResource = $quoteResource;
    }

    /**
     * @see CommonTaxCollector::updateItemTaxInfo
     *
     * Restore custom price after updateItemTaxInfo for our quotes.
     * Needed , because we save custom_price & original_custom_price in store base currency always.
     *
     * @param CommonTaxCollector $subject
     * @param callable $proceed
     * @param AbstractItem|Item $quoteItem
     * @param TaxDetailsItemInterface $itemTaxDetails
     * @param TaxDetailsItemInterface $baseItemTaxDetails
     * @param Store $store
     * @return CommonTaxCollector
     */
    public function aroundUpdateItemTaxInfo(
        CommonTaxCollector $subject,
        callable $proceed,
        $quoteItem,
        $itemTaxDetails,
        $baseItemTaxDetails,
        $store
    ): CommonTaxCollector {
        $customPrice = $quoteItem->getCustomPrice();

        $proceed($quoteItem, $itemTaxDetails, $baseItemTaxDetails, $store);

        if ($customPrice && $this->isAmastyQuoteItem($quoteItem)) {
            $quoteItem->setCustomPrice($customPrice);
        }

        return $subject;
    }

    private function isAmastyQuoteItem(AbstractItem $quoteItem): bool
    {
        return $quoteItem->getQuote() instanceof QuoteInterface
            || $quoteItem->getOptionByCode('amasty_quote_price')
            || $this->quoteResource->isAmastyQuote((int) $quoteItem->getQuote()->getId());
    }
}
