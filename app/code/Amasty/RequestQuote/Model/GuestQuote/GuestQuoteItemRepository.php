<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\GuestQuote;

use Amasty\RequestQuote\Api\GuestQuoteItemRepositoryInterface;
use Amasty\RequestQuote\Api\QuoteItemRepositoryInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResource;

class GuestQuoteItemRepository implements GuestQuoteItemRepositoryInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var QuoteIdMaskResource
     */
    private $quoteIdMaskResource;

    /**
     * @var QuoteItemRepositoryInterface
     */
    private $quoteItemRepository;

    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteIdMaskResource $quoteIdMaskResource,
        QuoteItemRepositoryInterface $quoteItemRepository
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteIdMaskResource = $quoteIdMaskResource;
        $this->quoteItemRepository = $quoteItemRepository;
    }

    public function getList(string $quoteMaskId): array
    {
        $quoteItemList = $this->quoteItemRepository->getList($this->retrieveQuoteId($quoteMaskId));
        foreach ($quoteItemList as $quoteItem) {
            $quoteItem->setQuoteId($quoteMaskId);
        }

        return $quoteItemList;
    }

    public function deleteById(string $quoteMaskId, int $itemId): bool
    {
        return $this->quoteItemRepository->deleteById(
            $this->retrieveQuoteId($quoteMaskId),
            $itemId
        );
    }

    private function retrieveQuoteId(string $quoteMaskId): int
    {
        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create();
        $this->quoteIdMaskResource->load($quoteIdMask, $quoteMaskId, 'masked_id');
        return (int)$quoteIdMask->getQuoteId();
    }
}
