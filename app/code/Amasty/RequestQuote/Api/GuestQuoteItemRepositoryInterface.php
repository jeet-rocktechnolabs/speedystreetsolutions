<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Api;

interface GuestQuoteItemRepositoryInterface
{
    /**
     * Lists items that are assigned to a specified quote cart.
     *
     * @param string $quoteId
     * @return \Amasty\RequestQuote\Api\Data\QuoteItemInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(string $quoteMaskId): array;

    /**
     * Remove the specified item from the specified quote cart.
     *
     * @param string $quoteMaskId
     * @param int $itemId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function deleteById(string $quoteMaskId, int $itemId): bool;
}
