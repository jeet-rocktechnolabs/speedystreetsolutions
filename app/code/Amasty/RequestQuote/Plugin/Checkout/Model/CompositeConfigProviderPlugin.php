<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Plugin\Checkout\Model;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Model\Quote\Frontend\GetAmastyQuote;
use Magento\Checkout\Model\CompositeConfigProvider;
use Magento\Checkout\Model\Session as CheckoutSession;

class CompositeConfigProviderPlugin
{
    /**
     * @var GetAmastyQuote
     */
    private $getAmastyQuote;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    public function __construct(
        GetAmastyQuote $getAmastyQuote,
        CheckoutSession $checkoutSession
    ) {
        $this->getAmastyQuote = $getAmastyQuote;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param CompositeConfigProvider $subject
     * @param array $config
     * @return array
     */
    public function afterGetConfig(CompositeConfigProvider $subject, array $config): array
    {
        if (($amastyQuote = $this->getAmastyQuote->execute($this->checkoutSession->getQuote()))
            && $amastyQuote->getData(QuoteInterface::SHIPPING_CONFIGURE)
            && !$amastyQuote->getData(QuoteInterface::SHIPPING_CAN_BE_MODIFIED)
            && isset($config['customerData']['addresses'])
        ) {
            $config['customerData']['addresses'] = [];
        }

        return $config;
    }
}
