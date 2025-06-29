<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Adminhtml\Quote\Create;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Data extends \Amasty\RequestQuote\Block\Adminhtml\Quote\Create\AbstractCreate
{
    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_localeCurrency;

    /**
     * @var \Amasty\RequestQuote\Model\Quote\Backend\Edit
     */
    private $quoteEditModel;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Amasty\RequestQuote\Model\Quote\Backend\Session $sessionQuote,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Amasty\RequestQuote\Model\Quote\Backend\Edit $quoteEditModel,
        array $data = []
    ) {
        $this->_currencyFactory = $currencyFactory;
        $this->_localeCurrency = $localeCurrency;
        parent::__construct($context, $sessionQuote, $priceCurrency, $data);
        $this->quoteEditModel = $quoteEditModel;
    }

    /**
     * @return string[]
     */
    public function getAvailableCurrencies()
    {
        $dirtyCodes = $this->getStore()->getAvailableCurrencyCodes();
        $codes = [];
        if (is_array($dirtyCodes) && count($dirtyCodes)) {
            $rates = $this->_currencyFactory->create()->getCurrencyRates(
                $this->_storeManager->getStore()->getBaseCurrency(),
                $dirtyCodes
            );
            foreach ($dirtyCodes as $code) {
                if (isset($rates[$code]) || $code == $this->_storeManager->getStore()->getBaseCurrencyCode()) {
                    $codes[] = $code;
                }
            }
        }
        return $codes;
    }

    /**
     * @param string $code
     * @return string
     */
    public function getCurrencyName($code)
    {
        return $this->_localeCurrency->getCurrency($code)->getName();
    }

    /**
     * @param string $code
     * @return string
     */
    public function getCurrencySymbol($code)
    {
        $currency = $this->_localeCurrency->getCurrency($code);
        return $currency->getSymbol() ? $currency->getSymbol() : $currency->getShortName();
    }

    /**
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        return $this->getStore()->getCurrentCurrencyCode();
    }

    /**
     * @return bool
     */
    public function isShippingConfigured(): bool
    {
        if ($this->quoteEditModel->hasData(QuoteInterface::SHIPPING_CONFIGURE)) {
            $result = (bool) $this->quoteEditModel->getData(QuoteInterface::SHIPPING_CONFIGURE);
        } elseif ($this->getQuote()->hasData(QuoteInterface::SHIPPING_CONFIGURE)) {
            $result = (bool) $this->getQuote()->getData(QuoteInterface::SHIPPING_CONFIGURE);
        } elseif ($this->getParentQuote()->hasData(QuoteInterface::SHIPPING_CONFIGURE)) {
            $result = (bool) $this->getParentQuote()->getData(QuoteInterface::SHIPPING_CONFIGURE);
        } else {
            $result = false;
        }

        return $result;
    }
}
