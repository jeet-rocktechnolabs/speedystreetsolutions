<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Adminhtml\Quote;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Model\Quote;
use Magento\Framework\Registry;

class Configuration extends \Magento\Backend\Block\Template
{
    /**
     * @var Registry
     */
    private $quoteSession;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    private $currency;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Amasty\RequestQuote\Model\Quote\Backend\Session $quoteSession,
        \Magento\Framework\Locale\CurrencyInterface $currency,
        \Magento\Framework\Json\EncoderInterface $encoder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->quoteSession = $quoteSession;
        $this->currency = $currency;
        $this->jsonEncoder = $encoder;
    }

    /**
     * @return \Amasty\RequestQuote\Api\Data\QuoteInterface
     */
    private function getQuote()
    {
        return $this->quoteSession->getQuote();
    }

    /**
     * @return \Amasty\RequestQuote\Api\Data\QuoteInterface|Quote
     */
    public function getParentQuote()
    {
        return $this->quoteSession->getParentQuote();
    }

    /**
     * @return string
     */
    public function getQuoteConfigJson()
    {
        $data = [];
        if ($this->getQuote()->getCustomerId()) {
            $data['customer_id'] = $this->getQuote()->getCustomerId();
        }

        $storeId = $this->getQuote()->getStoreId();
        if ($storeId !== null) {
            $data['store_id'] = $storeId;
            $data['currency_symbol'] = $this->getCurrencySymbol();
            $data['can_shipping_modified'] = $this->getShippingData(QuoteInterface::SHIPPING_CAN_BE_MODIFIED, true);
            $data['shipping_configured'] = $this->getShippingData(QuoteInterface::SHIPPING_CONFIGURE, true);
            $data['custom_fee'] = $this->getCustomFee();
            $data['custom_method_allowed'] = $this->getShippingData(QuoteInterface::CUSTOM_METHOD_ENABLED, false);
        }

        return $this->jsonEncoder->encode($data);
    }

    /**
     * @return string
     */
    private function getCurrencySymbol()
    {
        $currency = $this->currency->getCurrency($this->getQuote()->getStore()->getCurrentCurrencyCode());
        return $currency->getSymbol() ?: $currency->getShortName();
    }

    /**
     * @return float
     */
    private function getCustomFee()
    {
        return $this->getCustomData(QuoteInterface::CUSTOM_FEE) !== null
            ? (float) $this->getCustomData(QuoteInterface::CUSTOM_FEE)
            : 0;
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
        if ($this->getQuote()->hasData($key)) {
            $result = $this->getQuote()->getData($key);
        } elseif ($this->getParentQuote()->hasData($key)) {
            $result = $this->getParentQuote()->getData($key);
        } else {
            $result = null;
        }

        return $result;
    }
}
