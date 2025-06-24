<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Plugin\Quote\Model\ResourceModel\Quote\Item\Collection\Backend;

use Amasty\RequestQuote\Api\Data\QuoteInterface as AmastyQuoteInterface;
use Amasty\RequestQuote\Api\QuoteRepositoryInterface as AmastyQuoteRepository;
use Amasty\RequestQuote\Model\Source\Status;
use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\ResourceModel\Quote\Item\Collection as ItemCollection;

class RestrictProcessNotApproved
{
    private const COLLECTION_PROCESSED_FLAG = 'amasty_quote_status_check';

    /**
     * @var AmastyQuoteRepository
     */
    private $amastyQuoteRepository;

    /**
     * @var QuoteSession
     */
    private $quoteSession;

    /**
     * @var bool
     */
    private $inProgress = false;

    public function __construct(AmastyQuoteRepository $amastyQuoteRepository, QuoteSession $quoteSession)
    {
        $this->amastyQuoteRepository = $amastyQuoteRepository;
        $this->quoteSession = $quoteSession;
    }

    public function afterLoadWithFilter(ItemCollection $itemCollection): ItemCollection
    {
        if ($itemCollection->getFlag(self::COLLECTION_PROCESSED_FLAG) || $this->inProgress) {
            return $itemCollection;
        }

        $itemCollection->setFlag(self::COLLECTION_PROCESSED_FLAG, true);

        $quote = $itemCollection->getFirstItem()->getQuote();
        // check if quote used in order create (quote for order detect from magento quote session)
        if (!$quote || $quote->getId() != $this->quoteSession->getQuoteId()) {
            return $itemCollection;
        }

        $this->inProgress = true;
        try {
            /** @var AmastyQuoteInterface $amastyQuote */
            $amastyQuote = $this->amastyQuoteRepository->get($quote->getId());
        } catch (NoSuchEntityException $e) {
            return $itemCollection;
        }
        $this->inProgress = false;

        if ($amastyQuote->getStatus() !== Status::APPROVED) {
            $quote->setHasError(true);
            $quote->addMessage(__('Quote product(s) are no longer available because
                an order using this quote has already been placed.'));
        }

        return $itemCollection;
    }
}
