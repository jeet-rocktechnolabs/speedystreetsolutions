<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Api;

interface QuoteRepositoryInterface extends \Magento\Quote\Api\CartRepositoryInterface
{
    /**
     * @param int $quoteId
     * @param int[] $sharedStoreIds
     * @return \Amasty\RequestQuote\Api\Data\QuoteInterface
     */
    public function get($quoteId, array $sharedStoreIds = []);

    /**
     * @param int $quoteId
     * @return bool
     */
    public function approve($quoteId);

    /**
     * @param int $quoteId
     * @return bool
     */
    public function expire($quoteId);

    /**
     * Enables administrative users to list carts that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Amasty\RequestQuote\Api\Data\QuoteSearchResultsInterface
     */
    public function getRequestsList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ): \Amasty\RequestQuote\Api\Data\QuoteSearchResultsInterface;

    /**
     * @param int $quoteId
     * @param string $note
     * @return bool
     */
    public function addCustomerNote($quoteId, $note);

    /**
     * @param int $quoteId
     * @param string $note
     * @return bool
     */
    public function addAdminNote($quoteId, $note);

    /**
     * @param int $quoteId
     * @return bool
     */
    public function isAmastyQuote(int $quoteId): bool;
}
