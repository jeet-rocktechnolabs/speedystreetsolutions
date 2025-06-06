<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\GuestQuote;

use Amasty\RequestQuote\Api\Data\QuoteItemInterface;
use Amasty\RequestQuote\Api\GuestQuoteItemManagementInterface;
use Amasty\RequestQuote\Api\QuoteItemManagementInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResource;

class GuestQuoteItemManagement implements GuestQuoteItemManagementInterface
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
     * @var QuoteItemManagementInterface
     */
    private $quoteItemManagement;

    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteIdMaskResource $quoteIdMaskResource,
        QuoteItemManagementInterface $quoteItemManagement
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteIdMaskResource = $quoteIdMaskResource;
        $this->quoteItemManagement = $quoteItemManagement;
    }

    public function save(string $quoteMaskId, QuoteItemInterface $quoteItem): QuoteItemInterface
    {
        $quoteItem->setQuoteId($this->retrieveQuoteId($quoteMaskId));
        return $this->quoteItemManagement->save($quoteItem);
    }

    private function retrieveQuoteId(string $quoteMaskId): int
    {
        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create();
        $this->quoteIdMaskResource->load($quoteIdMask, $quoteMaskId, 'masked_id');
        return (int)$quoteIdMask->getQuoteId();
    }
}
