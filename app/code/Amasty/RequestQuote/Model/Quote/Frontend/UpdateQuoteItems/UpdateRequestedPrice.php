<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote\Frontend\UpdateQuoteItems;

use Amasty\RequestQuote\Api\Data\QuoteItemInterface;
use Amasty\RequestQuote\Helper\Cart as CartHelper;

/**
 * @api
 */
class UpdateRequestedPrice
{
    /**
     * @var CartHelper
     */
    private $cartHelper;

    public function __construct(CartHelper $cartHelper)
    {
        $this->cartHelper = $cartHelper;
    }

    public function execute(array $quoteItems): void
    {
        foreach ($quoteItems as $quoteItem) {
            $quoteItem->setAdditionalData(
                $this->cartHelper->updateAdditionalData(
                    $quoteItem->getAdditionalData(),
                    [QuoteItemInterface::REQUESTED_PRICE => $quoteItem->getPrice()]
                )
            );
        }
    }
}
