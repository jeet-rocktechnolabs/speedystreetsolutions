<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Adminhtml\Quote\Edit;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Registry;

abstract class AbstractEdit extends \Magento\Backend\Block\Widget
{
    /**
     * @var \Amasty\RequestQuote\Model\Quote\Backend\Session
     */
    protected $sessionQuote;

    /**
     * @var \Amasty\RequestQuote\Model\Quote\Backend\Edit
     */
    protected $quoteEditModel;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Amasty\RequestQuote\Model\Quote\Backend\Session $sessionQuote,
        \Amasty\RequestQuote\Model\Quote\Backend\Edit $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->sessionQuote = $sessionQuote;
        $this->quoteEditModel = $orderCreate;
        parent::__construct($context, $data);
    }

    /**
     * @return \Amasty\RequestQuote\Model\Quote\Backend\Edit
     */
    public function getQuoteEditModel()
    {
        return $this->quoteEditModel;
    }

    /**
     * @return \Amasty\RequestQuote\Model\Quote\Backend\Session
     */
    protected function getSession()
    {
        return $this->sessionQuote;
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->getSession()->getQuote(true);
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    public function getParentQuote()
    {
        return $this->getSession()->getParentQuote();
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->getSession()->getCustomerId();
    }

    /**
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->getSession()->getStore();
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->getQuote()->getStoreId();
    }

    /**
     * @param float $value
     * @return string
     */
    public function formatPrice($value, $convert = false)
    {
        if ($convert) {
            $value = $this->convertPrice($value, false);
        }
        return $this->priceCurrency->format(
            $value,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getStore(),
            $this->getQuote()->getQuoteCurrency()
        );
    }

    /**
     * @param Product $product
     * @return string
     */
    public function getItemPrice(Product $product)
    {
        $price = $product->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getValue();
        return $this->convertPrice($price);
    }

    /**
     * @param int|float $value
     * @param bool $format
     * @return string|int|float
     */
    public function convertPrice($value, $format = true)
    {
        return $format
            ? $this->priceCurrency->convertAndFormat(
                $value,
                true,
                PriceCurrencyInterface::DEFAULT_PRECISION,
                $this->getStore(),
                $this->getQuote()->getQuoteCurrency()
            )
            : $this->priceCurrency->convert($value, $this->getStore(), $this->getQuote()->getQuoteCurrency());
    }

    /**
     * @param $item
     * @return Product
     */
    public function getProduct($item)
    {
        if ($item instanceof Product) {
            $product = $item;
        } else {
            $product = $item->getProduct();
        }

        return $product;
    }

    /**
     * @return float
     */
    public function getAppliedDiscount()
    {
        return $this->getQuoteEditModel()->getData(QuoteInterface::DISCOUNT) !== null
            ? (float) $this->getQuoteEditModel()->getData(QuoteInterface::DISCOUNT)
            : (float) $this->getParentQuote()->getData(QuoteInterface::DISCOUNT);
    }

    /**
     * @return float
     */
    public function getAppliedSurcharge()
    {
        return $this->getQuoteEditModel()->getData(QuoteInterface::SURCHARGE) !== null
            ? (float) $this->getQuoteEditModel()->getData(QuoteInterface::SURCHARGE)
            : (float) $this->getParentQuote()->getData(QuoteInterface::SURCHARGE);
    }
}
