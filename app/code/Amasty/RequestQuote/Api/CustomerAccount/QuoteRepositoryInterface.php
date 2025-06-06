<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Api\CustomerAccount;

use Amasty\RequestQuote\Api\Data\CustomerAccount\QuoteInterface;
use Amasty\RequestQuote\Api\Data\CustomerAccount\QuoteSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface QuoteRepositoryInterface
{
    /**
     * @param int $quoteId
     * @return \Amasty\RequestQuote\Api\Data\CustomerAccount\QuoteInterface
     */
    public function get(int $quoteId): QuoteInterface;

    /**
     * Enables users to list carts that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Amasty\RequestQuote\Api\Data\CustomerAccount\QuoteSearchResultsInterface
     */
    public function getRequestsList(SearchCriteriaInterface $searchCriteria): QuoteSearchResultsInterface;
}
