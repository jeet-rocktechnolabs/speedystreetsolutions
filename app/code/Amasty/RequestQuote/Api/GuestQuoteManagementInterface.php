<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Api;

use Amasty\RequestQuote\Api\Data\AdvancedMergeResultInterface;
use Amasty\RequestQuote\Api\Data\QuoteInterface;

interface GuestQuoteManagementInterface
{
    /**
     * @param string $quoteMaskId
     * @return \Amasty\RequestQuote\Api\Data\QuoteInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function get(string $quoteMaskId): QuoteInterface;

    /**
     * Enable an guest user to create an empty cart and quote for an anonymous customer.
     *
     * @param int $status
     * @return string Masked Cart ID.
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function createEmptyQuoteCart(int $status): string;

    /**
     * Assigns a specified customer to a specified guest quote cart.
     *
     * @param string $quoteMaskId
     * @param int $customerId
     * @param int $storeId
     * @return boolean
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function assignCustomer(string $quoteMaskId, int $customerId, int $storeId): bool;

    /**
     * @param string $cartMaskId
     * @param string $quoteMaskId
     * @return \Amasty\RequestQuote\Api\Data\AdvancedMergeResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Amasty\RequestQuote\Exception\QuoteAlreadyInCartException
     */
    public function moveInQuote(string $cartMaskId, string $quoteMaskId): AdvancedMergeResultInterface;

    /**
     * @param string $quoteMaskId
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function updateCustomerInfo(string $quoteMaskId, string $email, string $firstName, string $lastName): bool;

    /**
     * @param string $quoteMaskId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function clear(string $quoteMaskId): bool;

    /**
     * @param string $quoteMaskId
     * @param string $customerNote
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function updateCustomerNote(string $quoteMaskId, string $customerNote): bool;
}
