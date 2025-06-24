<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote\CustomerAccount;

use Amasty\RequestQuote\Api\CustomerAccount\QuoteRepositoryInterface;
use Amasty\RequestQuote\Api\Data\CustomerAccount\QuoteInterface;
use Amasty\RequestQuote\Api\Data\CustomerAccount\QuoteSearchResultsInterface;
use Amasty\RequestQuote\Api\Data\CustomerAccount\QuoteSearchResultsInterfaceFactory;
use Amasty\RequestQuote\Api\Data\QuoteInterface as BasicQuoteInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

class QuoteRepository implements QuoteRepositoryInterface
{
    /**
     * @var \Amasty\RequestQuote\Api\QuoteRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var QuoteSearchResultsInterfaceFactory
     */
    private $quoteResultsDataFactory;

    /**
     * @var ConvertBasicQuote
     */
    private $convertBasicQuote;

    public function __construct(
        \Amasty\RequestQuote\Api\QuoteRepositoryInterface $quoteRepository,
        QuoteSearchResultsInterfaceFactory $quoteResultsDataFactory,
        ConvertBasicQuote $convertBasicQuote
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->quoteResultsDataFactory = $quoteResultsDataFactory;
        $this->convertBasicQuote = $convertBasicQuote;
    }

    public function get(int $quoteId): QuoteInterface
    {
        $quote = $this->quoteRepository->get($quoteId);
        return $this->convertBasicQuote->execute($quote);
    }

    public function getRequestsList(SearchCriteriaInterface $searchCriteria): QuoteSearchResultsInterface
    {
        $basicQuoteSearchResults = $this->quoteRepository->getRequestsList($searchCriteria);

        /** @var QuoteSearchResultsInterface $quoteSearchResults */
        $quoteSearchResults = $this->quoteResultsDataFactory->create();
        $quoteSearchResults->setSearchCriteria(clone $basicQuoteSearchResults->getSearchCriteria());
        $quoteSearchResults->setTotalCount($basicQuoteSearchResults->getTotalCount());
        $quoteSearchResults->setItems(array_map(function (BasicQuoteInterface $basicQuote) {
            return $this->convertBasicQuote->execute($basicQuote);
        }, $basicQuoteSearchResults->getItems()));

        return $quoteSearchResults;
    }
}
