<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Account\Quote\Item\Price;

use Amasty\RequestQuote\Model\Source\Status;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template;

class Renderer extends \Magento\Checkout\Block\Item\Price\Renderer
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Magento\Framework\Pricing\Adjustment\Calculator
     */
    private $calculator;

    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Pricing\Adjustment\Calculator $calculator,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->priceCurrency = $priceCurrency;
        $this->calculator = $calculator;
    }

    /**
     * @param $price
     * @param bool $convert
     *
     * @return float
     */
    public function convertPrice($price, $convert = true)
    {
        $currencyObject = $this->getItem()->getQuote()->getCurrency();
        $quoteStore = $this->getItem()->getQuote()->getStore();
        if ($convert) {
            $price = $this->priceCurrency
                ->getCurrency($quoteStore, $currencyObject->getBaseCurrencyCode())
                ->convert($price, $currencyObject->getQuoteCurrencyCode());
        }

        return $this->priceCurrency
            ->getCurrency($quoteStore, $currencyObject->getQuoteCurrencyCode())
            ->formatPrecision($price, PriceCurrencyInterface::DEFAULT_PRECISION, [], false);
    }

    /**
     * @return float
     */
    public function getOriginalPrice()
    {
        return $this->calculator->getAmount(
            $this->getItem()->getProduct()->getFinalPrice(),
            $this->getItem()->getProduct()
        )->getValue(['tax', 'weee_tax']);
    }
}
