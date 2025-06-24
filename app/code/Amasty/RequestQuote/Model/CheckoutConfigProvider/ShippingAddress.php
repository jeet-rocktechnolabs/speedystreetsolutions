<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\CheckoutConfigProvider;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Amasty\RequestQuote\Model\Customer\Address\CustomerAddressDataFormatterFactory;
use Amasty\RequestQuote\Model\Quote\Frontend\GetAmastyQuote;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class ShippingAddress implements ConfigProviderInterface
{
    /**
     * @var GetAmastyQuote
     */
    private $getAmastyQuote;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CustomerAddressDataFormatterFactory
     */
    private $customerAddressDataFormatterFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        GetAmastyQuote $getAmastyQuote,
        CheckoutSession $checkoutSession,
        CustomerAddressDataFormatterFactory $customerAddressDataFormatterFactory,
        LoggerInterface $logger
    ) {
        $this->getAmastyQuote = $getAmastyQuote;
        $this->checkoutSession = $checkoutSession;
        $this->customerAddressDataFormatterFactory = $customerAddressDataFormatterFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function getConfig(): array
    {
        $config = [];
        try {
            if (($amastyQuote = $this->getAmastyQuote->execute($this->checkoutSession->getQuote()))
                && $amastyQuote->isShippingConfigure()
            ) {
                $config = [
                    'amasty_quote' => [
                        'shipping_address' => $this->getShippingAddress()
                    ]
                ];
            }
        } catch (NoSuchEntityException | LocalizedException $e) {
            $this->logger->error($e->getMessage());
        }

        return $config;
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getShippingAddress(): array
    {
        $shippingAddress = $this->customerAddressDataFormatterFactory->create()->prepareAddress(
            $this->checkoutSession->getQuote()->getShippingAddress()->exportCustomerAddress()
        );
        unset($shippingAddress['region']);

        return $shippingAddress;
    }
}
