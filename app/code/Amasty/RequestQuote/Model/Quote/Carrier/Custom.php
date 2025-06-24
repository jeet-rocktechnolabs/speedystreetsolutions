<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote\Carrier;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Helper\Data;
use Amasty\RequestQuote\Model\Quote\Frontend\GetAmastyQuote;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Item;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result as RateResult;
use Magento\Shipping\Model\Rate\ResultFactory as RateResultFactory;
use Psr\Log\LoggerInterface;

class Custom extends AbstractCarrier implements CarrierInterface
{
    public const CODE = 'amasty_quote_custom_fee';

    /**
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * @var GetAmastyQuote
     */
    private $getAmastyQuote;

    /**
     * @var MethodFactory
     */
    private $rateMethodFactory;

    /**
     * @var RateResultFactory
     */
    private $rateResultFactory;

    /**
     * @var Data
     */
    private $configHelper;

    public function __construct(
        GetAmastyQuote $getAmastyQuote,
        Data $configHelper,
        RateResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        $this->getAmastyQuote = $getAmastyQuote;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->rateResultFactory = $rateResultFactory;
        $this->configHelper = $configHelper;
    }

    /**
     * @inheritDoc
     */
    public function collectRates(RateRequest $request)
    {
        /** @var RateResult $result */
        $result = $this->rateResultFactory->create();

        if ($this->isAvailable($request)) {
            $method = $this->createResultMethod($this->getCustomFee($request));
            $result->append($method);
        }

        return $result;
    }

    /**
     * @param RateRequest $request
     * @return float
     */
    protected function getCustomFee(RateRequest $request): float
    {
        if ($amastyQuote = $this->getAmastyQuote($request)) {
            $fee = (float) $amastyQuote->getData(QuoteInterface::CUSTOM_FEE) ?: 0;
        }

        return $fee ?? 0;
    }

    /**
     * @param RateRequest $request
     * @return bool
     */
    protected function isAvailable(RateRequest $request): bool
    {
        return ($amastyQuote = $this->getAmastyQuote($request))
            && $amastyQuote->getData(QuoteInterface::SHIPPING_CONFIGURE)
            && $amastyQuote->getData(QuoteInterface::CUSTOM_METHOD_ENABLED);
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [];
    }

    /**
     * Creates result method
     *
     * @param int|float $shippingPrice
     * @return Method
     */
    private function createResultMethod($shippingPrice)
    {
        /** @var Method $method */
        $method = $this->rateMethodFactory->create();

        $method->setCarrier(self::CODE);
        $method->setCarrierTitle(__('Custom'));

        $method->setMethod(self::CODE);
        $method->setMethodTitle($this->getRateMethodLabel());

        $method->setPrice($shippingPrice);
        $method->setCost($shippingPrice);

        return $method;
    }

    /**
     * @return string
     */
    protected function getRateMethodLabel()
    {
        return $this->configHelper->getRateMethodLabel($this->getStoreId());
    }

    /**
     * @return null
     */
    protected function getStoreId()
    {
        return null;
    }

    private function getAmastyQuote(RateRequest $request): ?QuoteInterface
    {
        /** @var Item $item */
        $item = $request->getAllItems()[0];
        if ($item) {
            return $this->getAmastyQuote->execute($item->getQuote());
        }

        return null;
    }
}
