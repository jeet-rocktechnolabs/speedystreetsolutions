<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Cart\Item\Price;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Amasty\RequestQuote\Model\Source\Status;
use Amasty\RequestQuote\Helper\Data as DataHelper;
use Magento\Tax\Helper\Data as TaxDataHelper;
use Magento\Checkout\Helper\Data as CheckoutDataHelper;

class Renderer extends \Magento\Checkout\Block\Item\Price\Renderer
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var DataHelper
     */
    private $helper;

    /**
     * @var TaxDataHelper
     */
    private $taxHelper;

    /**
     * @var CheckoutDataHelper
     */
    private $checkoutHelper;

    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        DataHelper $helper,
        TaxDataHelper $taxHelper,
        CheckoutDataHelper $checkoutHelper,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->priceCurrency = $priceCurrency;
        $this->helper = $helper;
        $this->taxHelper = $taxHelper;
        $this->checkoutHelper = $checkoutHelper;
    }

    /**
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->priceCurrency->getCurrencySymbol();
    }

    /**
     * @param $price
     * @param bool $delimiter
     *
     * @return float
     */
    public function convertPrice($price, $delimiter = true)
    {
        $currency = null;
        $options = [];
        if ($this->getItem()->getQuote()->getStatus() != Status::CREATED) {
            $currency = $this->getItem()->getQuote()->getCurrency()->getQuoteCurrencyCode();
        } else {
            $options['symbol'] = '';
        }

        if (!$delimiter) {
            $options['format'] = '###0.00';
        }

        $formattedPrice = $this->priceCurrency
            ->getCurrency($this->getItem()->getQuote()->getStore(), $currency)
            ->formatPrecision($price, PriceCurrencyInterface::DEFAULT_PRECISION, $options, false);
        if (!$delimiter) {
            $formattedPrice = str_replace(',', '.', $formattedPrice);

            if (substr_count($formattedPrice, '.') > 1) {
                $formattedPrice = preg_replace('/\./', '', $formattedPrice, 1);
            }
        }

        return $formattedPrice;
    }

    /**
     * @return float|string
     */
    public function getInputPrice()
    {
        $price = !$this->getItem()->hasCustomPrice()
            ? $this->priceCurrency->convert($this->getItem()->getProduct()->getFinalPrice($this->getItem()->getQty()))
            : $this->getItem()->getCalculationPriceOriginal();

        return $price ? $this->convertPrice($price, false) : '';
    }

    /**
     * @return bool
     */
    public function canCustomizePrice()
    {
        return $this->helper->isAllowCustomizePrice();
    }

    /**
     * @return bool
     */
    public function displayCartPriceExclTax()
    {
        return $this->taxHelper->displayCartPriceExclTax() || $this->taxHelper->displayCartBothPrices();
    }

    /**
     * @return bool
     */
    public function displayCartPriceInclTax()
    {
        return $this->taxHelper->displayCartPriceInclTax() || $this->taxHelper->displayCartBothPrices();
    }

    /**
     * @param float $price
     * @return string
     */
    public function formatPrice($price)
    {
        return $this->getCheckoutHelper()->formatPrice($price);
    }

    /**
     * @return CheckoutDataHelper
     */
    public function getCheckoutHelper()
    {
        return $this->checkoutHelper;
    }
}
