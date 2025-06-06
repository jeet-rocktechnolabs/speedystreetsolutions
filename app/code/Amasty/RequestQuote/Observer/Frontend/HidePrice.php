<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Observer\Frontend;

use Amasty\RequestQuote\Model\ResourceModel\Quote as QuoteResource;
use Amasty\RequestQuote\Model\HidePrice\Provider;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item;

class HidePrice implements ObserverInterface
{
    /**
     * @var Provider
     */
    private $hidePriceProvider;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    public function __construct(
        Provider $hidePriceProvider,
        QuoteResource $quoteResource
    ) {
        $this->hidePriceProvider = $hidePriceProvider;
        $this->quoteResource = $quoteResource;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $items = $observer->getData('items');

        if (isset($items[0]) && $this->quoteResource->isAmastyQuote($items[0]->getQuoteId())) {
            /** @var Item $item */
            foreach ($items as $item) {
                if ($this->hidePriceProvider->isHidePrice($item->getProduct())) {
                    $item->setCustomPrice(0)->setOriginalCustomPrice(0);
                }
            }
        }

        return $this;
    }
}
