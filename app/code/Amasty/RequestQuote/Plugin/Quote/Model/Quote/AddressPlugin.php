<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Plugin\Quote\Model\Quote;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Amasty\RequestQuote\Model\Quote\Frontend\GetAmastyQuote;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Address;

class AddressPlugin
{
    /**
     * @var GetAmastyQuote
     */
    private $getAmastyQuote;

    public function __construct(GetAmastyQuote $getAmastyQuote)
    {
        $this->getAmastyQuote = $getAmastyQuote;
    }

    public function beforeRequestShippingRates(Address $shippingAddress): void
    {
        $amastyQuote = $this->getAmastyQuote->execute($shippingAddress->getQuote());
        if ($amastyQuote && !$amastyQuote->getData(QuoteInterface::SHIPPING_CAN_BE_MODIFIED)) {
            $shippingRate = $shippingAddress->getShippingRateByCode($shippingAddress->getShippingMethod());
            if ($shippingRate) {
                $shippingAddress->setLimitCarrier($shippingRate->getCarrier());
            }
        }
    }
}
