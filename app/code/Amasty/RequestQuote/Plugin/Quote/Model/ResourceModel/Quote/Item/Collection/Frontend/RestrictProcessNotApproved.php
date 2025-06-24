<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Plugin\Quote\Model\ResourceModel\Quote\Item\Collection\Frontend;

use Amasty\RequestQuote\Api\Data\QuoteInterface as AmastyQuoteInterface;
use Amasty\RequestQuote\Api\QuoteRepositoryInterface as AmastyQuoteRepository;
use Amasty\RequestQuote\Model\Source\Status;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\ResourceModel\Quote\Item\Collection as ItemCollection;

class RestrictProcessNotApproved
{
    private const COLLECTION_PROCESSED_FLAG = 'amasty_quote_status_check';

    /**
     * @var AmastyQuoteRepository
     */
    private $amastyQuoteRepository;

    public function __construct(AmastyQuoteRepository $amastyQuoteRepository)
    {
        $this->amastyQuoteRepository = $amastyQuoteRepository;
    }

    public function afterLoadWithFilter(ItemCollection $itemCollection): ItemCollection
    {
        if ($itemCollection->getFlag(self::COLLECTION_PROCESSED_FLAG)) {
            return $itemCollection;
        }

        $itemCollection->setFlag(self::COLLECTION_PROCESSED_FLAG, true);

        $quote = $itemCollection->getFirstItem()->getQuote();
        if (!$quote || $this->amastyQuoteRepository->isAmastyQuote((int)$quote->getId())) {
            return $itemCollection;
        }

        $invalidAmastyQuoteStatus = false;
        /** @var QuoteItem $quoteItem */
        foreach ($itemCollection as $quoteItem) {
            if ($amastyQuoteIdOption = $quoteItem->getOptionByCode('amasty_quote_id')) {
                /** @var AmastyQuoteInterface $amastyQuote */
                 $amastyQuote = $this->amastyQuoteRepository->get($amastyQuoteIdOption->getValue());
                if ($amastyQuote->getStatus() !== Status::APPROVED) {
                    $quoteItem->setHasError(true);
                    $quoteItem->addMessage(__('Unavailable quote item, remove it to place the order!'));
                    $invalidAmastyQuoteStatus = true;
                }
            }
        }

        if ($invalidAmastyQuoteStatus) {
            $quote->setHasError(true);
            $quote->addMessage(__('Quote product(s) are no longer available because
                an order using this quote has already been placed.'));
        }

        return $itemCollection;
    }
}
