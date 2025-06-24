<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote\Frontend;

use Amasty\RequestQuote\Api\Data\QuoteInterface as AmastyQuote;
use Amasty\RequestQuote\Api\QuoteRepositoryInterface as AmastyQuoteRepository;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;

/**
 * Try get amasty quote from magento quote.
 * @api
 */
class GetAmastyQuote
{
    /**
     * @var AmastyQuoteRepository
     */
    private $amastyQuoteRepository;

    public function __construct(AmastyQuoteRepository $amastyQuoteRepository)
    {
        $this->amastyQuoteRepository = $amastyQuoteRepository;
    }

    /**
     * @param CartInterface|Quote $quote
     */
    public function execute(CartInterface $quote): ?AmastyQuote
    {
        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            if ($amastyQuoteIdOption = $quoteItem->getOptionByCode('amasty_quote_id')) {
                return $this->amastyQuoteRepository->get($amastyQuoteIdOption->getValue());
            }
        }

        return null;
    }
}
