<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Plugin\RequestQuote\Model\QuoteItemRepository;

use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Amasty\RequestQuote\Model\QuoteItemRepository;
use Magento\Framework\Exception\NoSuchEntityException;

class ValidateQuoteStatus
{
    /**
     * @var QuoteRepositoryInterface
     */
    private $quoteRepository;

    public function __construct(QuoteRepositoryInterface $quoteRepository)
    {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDeleteById(QuoteItemRepository $quoteItemRepository, int $quoteId): void
    {
        // validate for status for \Amasty\RequestQuote\Model\Source\Status::CREATED
        $this->quoteRepository->getActive($quoteId);
    }

    /**
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetList(QuoteItemRepository $quoteItemRepository, int $quoteId): void
    {
        // validate for status for \Amasty\RequestQuote\Model\Source\Status::CREATED
        $this->quoteRepository->getActive($quoteId);
    }
}
