<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Plugin\Quote\Model\Quote;

use Amasty\RequestQuote\Model\Quote;
use Amasty\RequestQuote\Model\Quote\Frontend\GetAmastyQuote;
use Amasty\RequestQuote\Model\Source\Status;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Amasty\RequestQuote\Model\ResourceModel\Quote as QuoteResource;

class Item
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var null|string
     */
    private $currencyCode = null;

    /**
     * @var QuoteRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var array
     */
    private $amastyQuotes = [];

    /**
     * @var GetAmastyQuote
     */
    private $getAmastyQuote;

    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        StoreManagerInterface $storeManager,
        QuoteRepositoryInterface $quoteRepository,
        QuoteResource $quoteResource,
        GetAmastyQuote $getAmastyQuote
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->storeManager = $storeManager;
        $this->quoteRepository = $quoteRepository;
        $this->quoteResource = $quoteResource;
        $this->getAmastyQuote = $getAmastyQuote;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $subject
     * @param $price
     *
     * @return float
     */
    public function afterGetCalculationPrice(\Magento\Quote\Model\Quote\Item $subject, $price)
    {
        if ($subject->hasCustomPrice() && $this->isAmastyCustomPrice($subject)) {
            $price = $this->getCurrencyFrom($subject)->convert(
                $price,
                $this->priceCurrency->getCurrency(
                    null,
                    $this->getCurrencyCode($subject)
                )
            );
        }

        return $price;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $subject
     * @param $price
     *
     * @return float
     */
    public function afterGetCalculationPriceOriginal(\Magento\Quote\Model\Quote\Item $subject, $price)
    {
        if ($subject->hasOriginalCustomPrice() && $this->isAmastyCustomPrice($subject)) {
            $price = $this->getCurrencyFrom($subject)->convert(
                $price,
                $this->priceCurrency->getCurrency(
                    null,
                    $this->getCurrencyCode($subject)
                )
            );
        }

        return $price;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $subject
     * @param $price
     *
     * @return float
     */
    public function afterGetBaseCalculationPriceOriginal(\Magento\Quote\Model\Quote\Item $subject, $price)
    {
        if ($subject->hasOriginalCustomPrice() && $this->isAmastyCustomPrice($subject)) {
            $price = $subject->getOriginalCustomPrice();
        }

        return $price;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     *
     * @return null|string
     */
    public function getCurrencyCode(\Magento\Quote\Model\Quote\Item $item)
    {
        if ($this->currencyCode === null && $item->getQuote()->getStatus() != Status::CREATED) {
            $this->currencyCode = $item->getQuote()->getQuoteCurrencyCode();
        }

        return $this->currencyCode;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $subject
     * @param bool $result
     *
     * @return bool
     */
    public function afterIsChildrenCalculated(\Magento\Quote\Model\Quote\Item $subject, $result)
    {
        if ($this->isAmastyQuote($subject->getQuote()->getId())
            || $this->getAmastyQuote->execute($subject->getQuote())
        ) {
            $result = false;
        }

        return $result;
    }

    /**
     * @param int $quoteId
     *
     * @return bool
     */
    private function isAmastyQuote($quoteId)
    {
        if (!isset($this->amastyQuotes[$quoteId])) {
            $this->amastyQuotes[$quoteId] = $this->quoteResource->isAmastyQuote($quoteId);
        }

        return $this->amastyQuotes[$quoteId];
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     *
     * @return bool
     */
    protected function isAmastyCustomPrice(\Magento\Quote\Model\Quote\Item $item)
    {
        if ($item->getOptionByCode('amasty_quote_price')) {
            $result = true;
        } else {
            try {
                if ($item->getQuote() instanceof Quote) {
                    $quote = $item->getQuote();
                } else {
                    // detect amasty quote
                    $quote = $this->quoteRepository->get($item->getQuote()->getId());
                }
                $result = Status::CREATED == $quote->getStatus();
            } catch (NoSuchEntityException $entityException) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     *
     * @return \Magento\Directory\Model\Currency
     */
    public function getCurrencyFrom($item)
    {
        return $this->storeManager->getStore(
            $item->getQuote()->getStoreId()
        )->getBaseCurrency();
    }
}
