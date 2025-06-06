<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\GuestQuote;

use Amasty\RequestQuote\Api\GuestQuoteServiceInterface;
use Amasty\RequestQuote\Api\QuoteServiceInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResource;

class GuestQuoteService implements GuestQuoteServiceInterface
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
     * @var QuoteServiceInterface
     */
    private $quoteService;

    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteIdMaskResource $quoteIdMaskResource,
        QuoteServiceInterface $quoteService
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteIdMaskResource = $quoteIdMaskResource;
        $this->quoteService = $quoteService;
    }

    /**
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function submit(string $quoteMaskId): string
    {
        return $this->quoteService->submit($this->retrieveQuoteId($quoteMaskId));
    }

    private function retrieveQuoteId(string $quoteMaskId): int
    {
        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create();
        $this->quoteIdMaskResource->load($quoteIdMask, $quoteMaskId, 'masked_id');
        return (int)$quoteIdMask->getQuoteId();
    }
}
