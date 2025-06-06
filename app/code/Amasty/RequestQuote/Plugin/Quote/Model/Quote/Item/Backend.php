<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Plugin\Quote\Model\Quote\Item;

use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Amasty\RequestQuote\Model\Quote\Frontend\GetAmastyQuote;
use Amasty\RequestQuote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magento\Store\Model\StoreManagerInterface;

class Backend extends \Amasty\RequestQuote\Plugin\Quote\Model\Quote\Item
{
    /**
     * @var QuoteSession
     */
    private $quoteSession;

    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        StoreManagerInterface $storeManager,
        QuoteRepositoryInterface $quoteRepository,
        QuoteResource $quoteResource,
        QuoteSession $quoteSession,
        GetAmastyQuote $getAmastyQuote
    ) {
        parent::__construct($priceCurrency, $storeManager, $quoteRepository, $quoteResource, $getAmastyQuote);
        $this->quoteSession = $quoteSession;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     *
     * @return null|string
     */
    public function getCurrencyCode(\Magento\Quote\Model\Quote\Item $item)
    {
        return $this->quoteSession->getCurrencyId() && $this->quoteSession->getCurrencyId() != 'false'
            ? $this->quoteSession->getCurrencyId()
            : $item->getQuote()->getQuoteCurrencyCode();
    }

    /**
     * @inheritdoc
     */
    public function getCurrencyFrom($item)
    {
        return $this->quoteSession->getStoreId()
            ? $this->quoteSession->getStore()->getBaseCurrency()
            : parent::getCurrencyFrom($item);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $subject
     * @param bool $result
     *
     * @return bool
     */
    public function afterIsChildrenCalculated(\Magento\Quote\Model\Quote\Item $subject, $result)
    {
        if ($this->isAmastyCustomPrice($subject)) {
            $result = false;
        }

        return $result;
    }
}
