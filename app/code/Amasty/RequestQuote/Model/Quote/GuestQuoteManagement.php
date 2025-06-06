<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote;

use Amasty\RequestQuote\Api\Data\AdvancedMergeResultInterface;
use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Api\GuestQuoteManagementInterface;
use Amasty\RequestQuote\Api\QuoteManagementInterface;
use Amasty\RequestQuote\Exception\QuoteAlreadyInCartException;
use Amasty\RequestQuote\Model\ConfigProvider;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResource;

class GuestQuoteManagement implements GuestQuoteManagementInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var QuoteManagementInterface
     */
    private $quoteManagement;

    /**
     * @var QuoteIdMaskResource
     */
    private $quoteIdMaskResource;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteManagementInterface $quoteManagement,
        QuoteIdMaskResource $quoteIdMaskResource,
        ConfigProvider $configProvider
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteManagement = $quoteManagement;
        $this->quoteIdMaskResource = $quoteIdMaskResource;
        $this->configProvider = $configProvider;
    }

    public function get(string $quoteMaskId): QuoteInterface
    {
        return $this->quoteManagement->getQuoteCart($this->retrieveQuoteId($quoteMaskId));
    }

    /**
     * @throws CouldNotSaveException
     */
    public function createEmptyQuoteCart(int $status): string
    {
        $quoteId = $this->quoteManagement->createEmptyQuoteCart($status);

        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create();
        $quoteIdMask->setQuoteId($quoteId);
        $this->quoteIdMaskResource->save($quoteIdMask);

        return $quoteIdMask->getMaskedId();
    }

    /**
     * @throws NoSuchEntityException
     * @throws StateException
     * @throws LocalizedException
     */
    public function assignCustomer(string $quoteMaskId, int $customerId, int $storeId): bool
    {
        return $this->quoteManagement->assignCustomer($this->retrieveQuoteId($quoteMaskId), $customerId, $storeId);
    }

    /**
     * @throws NoSuchEntityException
     * @throws StateException
     * @throws QuoteAlreadyInCartException
     */
    public function moveInQuote(string $cartMaskId, string $quoteMaskId): AdvancedMergeResultInterface
    {
        return $this->quoteManagement->moveInQuote(
            $this->retrieveQuoteId($cartMaskId),
            $this->retrieveQuoteId($quoteMaskId)
        );
    }

    /**
     * @throws NoSuchEntityException
     * @throws StateException
     * @throws InputException
     */
    public function updateCustomerInfo(string $quoteMaskId, string $email, string $firstName, string $lastName): bool
    {
        if ($this->configProvider->isAllowedCreateAccountForGuest()) {
            throw new StateException(
                __('User data change is restricted. Please create a customer account to make changes.')
            );
        }

        return $this->quoteManagement->updateCustomerInfo(
            $this->retrieveQuoteId($quoteMaskId),
            $email,
            $firstName,
            $lastName
        );
    }

    /**
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function clear(string $quoteMaskId): bool
    {
        return $this->quoteManagement->clear($this->retrieveQuoteId($quoteMaskId));
    }

    /**
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function updateCustomerNote(string $quoteMaskId, string $customerNote): bool
    {
        return $this->quoteManagement->updateCustomerNote(
            $this->retrieveQuoteId($quoteMaskId),
            $customerNote
        );
    }

    private function retrieveQuoteId(string $quoteMaskId): int
    {
        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create();
        $this->quoteIdMaskResource->load($quoteIdMask, $quoteMaskId, 'masked_id');
        return (int)$quoteIdMask->getQuoteId();
    }
}
