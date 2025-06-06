<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Plugin\Multishipping\Helper;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Model\Quote\Frontend\GetAmastyQuote;
use Magento\Checkout\Model\Session;
use Magento\Multishipping\Helper\Data as MultishippingHelper;

class DataPlugin
{
    /**
     * @var GetAmastyQuote
     */
    private $getAmastyQuote;

    /**
     * @var Session
     */
    private $checkoutSession;

    public function __construct(
        GetAmastyQuote $getAmastyQuote,
        Session $checkoutSession
    ) {
        $this->getAmastyQuote = $getAmastyQuote;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param MultishippingHelper $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsMultishippingCheckoutAvailable(MultishippingHelper $subject, bool $result): bool
    {
        if ($result && $amastyQuote = $this->getAmastyQuote->execute($this->checkoutSession->getQuote())) {
            $result = (bool) $amastyQuote->getData(QuoteInterface::SHIPPING_CAN_BE_MODIFIED);
        }

        return $result;
    }
}
