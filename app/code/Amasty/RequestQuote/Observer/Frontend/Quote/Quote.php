<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Observer\Frontend\Quote;

class Quote implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
        $quoteItem = $observer->getEvent()->getQuoteItem();
        if ($quoteItem->getOptionByCode('amasty_quote_price')
            && $quoteItem->getOrigData('qty') != $quoteItem->getData('qty')
        ) {
            $quoteItem->setHasError(true);
            $quoteItem->setMessage(__('It is not possible to edit items qty of the approved Quote.'));
        }

        return $this;
    }
}
