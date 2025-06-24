<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Api;

interface QuoteItemRepositoryInterface
{
    /**
     * @param int $quoteItemId
     * @param string $note
     * @return bool
     */
    public function addCustomerNote($quoteItemId, $note);

    /**
     * @param int $quoteItemId
     * @param string $note
     * @return bool
     */
    public function addAdminNote($quoteItemId, $note);

    /**
     * @param int $quoteId
     * @param int $itemId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function deleteById(int $quoteId, int $itemId): bool;

    /**
     * Lists items that are assigned to a specified quote cart.
     *
     * @param int $quoteId
     * @return \Amasty\RequestQuote\Api\Data\QuoteItemInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function getList(int $quoteId): array;
}
