<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Adminhtml\Quote\Create\Shipping\Method;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\ScopeInterface;

class Form extends \Amasty\RequestQuote\Block\Adminhtml\Quote\Create\AbstractCreate
{
    /**
     * Shipping rates
     *
     * @var array
     */
    protected $rates;

    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxData = null;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Amasty\RequestQuote\Model\Quote\Backend\Edit
     */
    private $quoteEditModel;

    public function __construct(
        \Amasty\RequestQuote\Model\Quote\Backend\Edit $quoteEditModel,
        \Magento\Backend\Block\Template\Context $context,
        \Amasty\RequestQuote\Model\Quote\Backend\Session $sessionQuote,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Tax\Helper\Data $taxData,
        array $data = []
    ) {
        $this->taxData = $taxData;
        parent::__construct($context, $sessionQuote, $priceCurrency, $data);
        $this->quoteEditModel = $quoteEditModel;
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_shipping_method_form');
    }

    /**
     * Retrieve quote shipping address model
     *
     * @return \Magento\Quote\Model\Quote\Address
     */
    public function getAddress()
    {
        return $this->getQuote()->getShippingAddress();
    }

    /**
     * Retrieve array of shipping rates groups
     *
     * @return array
     */
    public function getShippingRates()
    {
        if (empty($this->rates)) {
            $this->rates = $this->getAddress()->getGroupedAllShippingRates();
        }

        return $this->rates;
    }

    /**
     * Retrieve carrier name from store configuration
     *
     * @param string $carrierCode
     * @param array $rates
     * @return string
     */
    public function getCarrierName($carrierCode, $rates = [])
    {
        if (isset($rates[0])) {
            $name = $rates[0]->getCarrierTitle();
        } else {
            $name = $this->_scopeConfig->getValue(
                'carriers/' . $carrierCode . '/title',
                ScopeInterface::SCOPE_STORE,
                $this->getStore()->getId()
            );

            if (!$name) {
                $name = $carrierCode;
            } else {
                $name = __($name);
            }
        }

        return $name;
    }

    /**
     * Retrieve current selected shipping method
     *
     * @return string
     */
    public function getShippingMethod()
    {
        return $this->getAddress()->getShippingMethod();
    }

    /**
     * Check activity of method by code
     *
     * @param string $code
     * @return bool
     */
    public function isMethodActive($code)
    {
        return $code === $this->getShippingMethod();
    }

    /**
     * Retrieve rate of active shipping method
     *
     * @return \Magento\Quote\Model\Quote\Address\Rate|false
     */
    public function getActiveMethodRate()
    {
        $rates = $this->getShippingRates();
        if (is_array($rates)) {
            foreach ($rates as $group) {
                foreach ($group as $rate) {
                    if ($rate->getCode() == $this->getShippingMethod()) {
                        return $rate;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Get rate request
     *
     * @return mixed
     */
    public function getIsRateRequest()
    {
        return $this->getRequest()->getParam('collect_shipping_rates');
    }

    /**
     * Get shipping price
     *
     * @param float $price
     * @param bool $flag
     * @return float
     */
    public function getShippingPrice($price, $flag)
    {
        return $this->priceCurrency->convertAndFormat(
            $this->taxData->getShippingPrice(
                $price,
                $flag,
                $this->getAddress(),
                null,
                $this->getAddress()->getQuote()->getStore()
            ),
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getQuote()->getStore()
        );
    }

    /**
     * @return string
     */
    public function getPriceSymbol()
    {
        return $this->priceCurrency->getCurrencySymbol($this->getQuote()->getStore());
    }

    /**
     * @return bool
     */
    public function isShippingCanBeModified(): bool
    {
        return $this->getShippingData(QuoteInterface::SHIPPING_CAN_BE_MODIFIED, true);
    }

    /**
     * @return bool
     */
    public function isCustomMethodEnabled(): bool
    {
        return $this->getShippingData(QuoteInterface::CUSTOM_METHOD_ENABLED, false);
    }

    /**
     * @param string $key
     * @param bool $defaultValue
     * @return bool
     */
    protected function getShippingData(string $key, bool $defaultValue): bool
    {
        return $this->getCustomData($key) !== null ? (bool) $this->getCustomData($key) : $defaultValue;
    }

    /**
     * @param $key
     * @return mixed
     */
    private function getCustomData($key)
    {
        if ($this->quoteEditModel->hasData($key)) {
            $result = $this->quoteEditModel->getData($key);
        } elseif ($this->getQuote()->hasData($key)) {
            $result = $this->getQuote()->getData($key);
        } elseif ($this->getParentQuote()->hasData($key)) {
            $result = $this->getParentQuote()->getData($key);
        } else {
            $result = null;
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function displayShippingPriceIncludingTax()
    {
        return $this->taxData->displayShippingPriceIncludingTax();
    }

    /**
     * @return bool
     */
    public function displayShippingBothPrices()
    {
        return $this->taxData->displayShippingBothPrices();
    }
}
