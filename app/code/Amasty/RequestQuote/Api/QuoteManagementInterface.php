<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Api;

use Amasty\RequestQuote\Api\Data\AdvancedMergeResultInterface;
use Amasty\RequestQuote\Api\Data\QuoteInterface;

interface QuoteManagementInterface
{
    /**
     * @param int $quoteId
     * @return \Amasty\RequestQuote\Api\Data\QuoteInterface
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuoteCart(int $quoteId): QuoteInterface;

    /**
     * Returns information for the quote cart for a specified customer.
     *
     * @param int $customerId
     * @return \Amasty\RequestQuote\Api\Data\QuoteInterface
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuoteCartForCustomer(int $customerId): QuoteInterface;

    /**
     * Creates an empty anonymous quote.
     *
     * @param int $status
     * @return int
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function createEmptyQuoteCart(int $status): int;

    /**
     * Creates an empty quote for a specified customer if customer does not have a cart yet.
     *
     * @param int $customerId
     * @param int $status
     * @return int
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function createEmptyQuoteCartForCustomer(int $customerId, int $status): int;

    /**
     * Assigns a specified customer to a specified quote cart.
     *
     * @param int $quoteId
     * @param int $customerId
     * @param int $storeId
     * @return boolean
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function assignCustomer(int $quoteId, int $customerId, int $storeId): bool;

    /**
     * @param int $quoteId
     * @param int $cartId
     * @param int $customerId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Amasty\RequestQuote\Exception\NotApprovedQuoteException
     * @throws \Amasty\RequestQuote\Exception\QuoteAlreadyInCartException
     */
    public function moveInCart(int $quoteId, int $cartId, int $customerId): bool;

    /**
     * @param int $cartId
     * @param int $quoteId
     * @return \Amasty\RequestQuote\Api\Data\AdvancedMergeResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Amasty\RequestQuote\Exception\QuoteAlreadyInCartException
     */
    public function moveInQuote(int $cartId, int $quoteId): AdvancedMergeResultInterface;

    /**
     * @param int $quoteId
     * @param int|null $customerId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function cancelQuote(int $quoteId, int $customerId): bool;

    /**
     * @param int $quoteId
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function updateCustomerInfo(int $quoteId, string $email, string $firstName, string $lastName): bool;

    /**
     * @param int $quoteId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function clear(int $quoteId): bool;

    /**
     * @param int $quoteId
     * @param string $customerNote
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function updateCustomerNote(int $quoteId, string $customerNote): bool;
}
