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
use Amasty\RequestQuote\Api\QuoteManagementInterface;
use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Amasty\RequestQuote\Exception\NotApprovedQuoteException;
use Amasty\RequestQuote\Exception\QuoteAlreadyInCartException;
use Amasty\RequestQuote\Model\ConfigProvider;
use Amasty\RequestQuote\Model\Quote\Action\MoveInCart;
use Amasty\RequestQuote\Model\Quote\Action\MoveInQuote;
use Amasty\RequestQuote\Model\QuoteFactory;
use Amasty\RequestQuote\Model\Source\Status;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\GroupManagement;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Validator\EmailAddress as EmailAddressValidator;
use Magento\Store\Model\StoreManagerInterface;

class QuoteManagement implements QuoteManagementInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var QuoteRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var MoveInCart
     */
    private $moveInCart;

    /**
     * @var MoveInQuote
     */
    private $moveInQuote;

    /**
     * @var EmailAddressValidator
     */
    private $emailAddressValidator;

    public function __construct(
        ConfigProvider $configProvider,
        StoreManagerInterface $storeManager,
        QuoteRepositoryInterface $quoteRepository,
        CustomerRepositoryInterface $customerRepository,
        CustomerRegistry $customerRegistry,
        QuoteFactory $quoteFactory,
        MoveInCart $moveInCart,
        MoveInQuote $moveInQuote,
        EmailAddressValidator $emailAddressValidator
    ) {
        $this->configProvider = $configProvider;
        $this->storeManager = $storeManager;
        $this->quoteRepository = $quoteRepository;
        $this->customerRepository = $customerRepository;
        $this->customerRegistry = $customerRegistry;
        $this->quoteFactory = $quoteFactory;
        $this->moveInCart = $moveInCart;
        $this->moveInQuote = $moveInQuote;
        $this->emailAddressValidator = $emailAddressValidator;
    }

    /**
     * @throws StateException
     */
    public function getQuoteCart(int $quoteId): QuoteInterface
    {
        if (!$this->isEnabled()) {
            throw new StateException(__('Quote Cart has been disabled.'));
        }

        if (!$this->isCustomerGroupAllowed(GroupManagement::NOT_LOGGED_IN_ID)) {
            throw new StateException(__('Quote Cart has been disabled for this customer group.'));
        }

        /** @var QuoteInterface $quote */
        $quote = $this->quoteRepository->get($quoteId);
        if ($quote->getStatus() !== Status::CREATED) {
            throw new StateException(__('Quote Cart has invalid status.'));
        }

        return $quote;
    }

    /**
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function getQuoteCartForCustomer(int $customerId): QuoteInterface
    {
        if (!$this->isEnabled()) {
            throw new StateException(__('Quote Cart has been disabled.'));
        }

        $quote = $this->quoteRepository->getActiveForCustomer($customerId);

        if (!$this->isCustomerGroupAllowed((int)$quote->getCustomer()->getGroupId())) {
            throw new StateException(__('Quote Cart has been disabled for this customer group.'));
        }

        return $quote;
    }

    /**
     * @throws StateException
     * @throws CouldNotSaveException
     */
    public function createEmptyQuoteCart(int $status): int
    {
        if (!$this->isEnabled()) {
            throw new StateException(__('Quote Cart has been disabled.'));
        }

        if (!$this->isCustomerGroupAllowed(GroupManagement::NOT_LOGGED_IN_ID)) {
            throw new StateException(__('Quote Cart has been disabled for this customer group.'));
        }

        $quote = $this->createAnonymousCart(
            (int)$this->storeManager->getStore()->getStoreId(),
            $status
        );

        try {
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('The quote can\'t be created.'));
        }

        return (int)$quote->getId();
    }

    /**
     * @throws StateException
     * @throws CouldNotSaveException
     */
    public function createEmptyQuoteCartForCustomer(int $customerId, int $status): int
    {
        if (!$this->isEnabled()) {
            throw new StateException(__('Quote Cart has been disabled.'));
        }

        $quote = $this->getCustomerQuoteCart(
            $customerId,
            (int)$this->storeManager->getStore()->getStoreId(),
            $status
        );

        if (!$this->isCustomerGroupAllowed((int)$quote->getCustomer()->getGroupId())) {
            throw new StateException(__('Quote Cart has been disabled for this customer group.'));
        }

        try {
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__("The quote can't be created."));
        }

        return (int)$quote->getId();
    }

    /**
     * @throws NoSuchEntityException
     * @throws StateException
     * @throws LocalizedException
     */
    public function assignCustomer(int $quoteId, int $customerId, int $storeId): bool
    {
        if (!$this->isEnabled()) {
            throw new StateException(__('Quote Cart has been disabled.'));
        }

        $customer = $this->customerRepository->getById($customerId);
        if (!$this->isCustomerGroupAllowed((int)$customer->getGroupId())) {
            throw new StateException(__('Quote Cart has been disabled for this customer group.'));
        }

        if (!in_array($storeId, $this->customerRegistry->retrieve($customerId)->getSharedStoreIds())) {
            throw new StateException(
                __("The customer can't be assigned to the cart. The cart belongs to a different store.")
            );
        }

        $quote = $this->quoteRepository->get($quoteId);
        if ($quote->getCustomerId()) {
            throw new StateException(
                __("The customer can't be assigned to the cart because the cart isn't anonymous.")
            );
        }

        // merge quotes only for active on frontend quote
        if ($quote->getIsActive()) {
            try {
                $customerActiveQuote = $this->quoteRepository->getActiveForCustomer($customerId);
                $quote->merge($customerActiveQuote);
                $this->quoteRepository->delete($customerActiveQuote);

                // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
            } catch (NoSuchEntityException $e) {
            }
        }

        $quote->setCustomer($customer);
        $quote->setCustomerIsGuest(0);

        $this->quoteRepository->save($quote);

        return true;
    }

    /**
     * @throws NoSuchEntityException
     * @throws StateException
     * @throws NotApprovedQuoteException
     * @throws QuoteAlreadyInCartException
     */
    public function moveInCart(int $quoteId, int $cartId, int $customerId): bool
    {
        if (!$this->isEnabled()) {
            throw new StateException(__('Quote Cart has been disabled.'));
        }

        $quote = $this->quoteRepository->get($quoteId);
        if ((int)$quote->getCustomerId() !== $customerId) {
            throw new StateException(__(
                'The current user cannot perform operations on quote "%quote_id"',
                ['quote_id' => $quoteId]
            ));
        }
        if (!$this->isCustomerGroupAllowed((int)$quote->getCustomer()->getGroupId())) {
            throw new StateException(__('Quote Cart has been disabled for this customer group.'));
        }

        $this->moveInCart->execute($quoteId, $cartId);

        return true;
    }

    /**
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function moveInQuote(int $cartId, int $quoteId): AdvancedMergeResultInterface
    {
        if (!$this->isEnabled()) {
            throw new StateException(__('Quote Cart has been disabled.'));
        }

        $quote = $this->quoteRepository->get($quoteId);
        if (!$this->isCustomerGroupAllowed((int)$quote->getCustomer()->getGroupId())) {
            throw new StateException(__('Quote Cart has been disabled for this customer group.'));
        }

        return $this->moveInQuote->execute($cartId, $quoteId);
    }

    /**
     * @throws StateException
     * @throws NoSuchEntityException
     */
    public function cancelQuote(int $quoteId, ?int $customerId = null): bool
    {
        if (!$this->isEnabled()) {
            throw new StateException(__('Quote Cart has been disabled.'));
        }

        $quote = $this->quoteRepository->get($quoteId);
        if ($customerId !== null && (int)$quote->getCustomerId() !== $customerId) {
            throw new StateException(__(
                'The current user cannot perform operations on quote "%quote_id"',
                ['quote_id' => $quoteId]
            ));
        }
        if (!$this->isCustomerGroupAllowed((int)$quote->getCustomer()->getGroupId())) {
            throw new StateException(__('Quote Cart has been disabled for this customer group.'));
        }

        if (!$quote->canClose()) {
            throw new StateException(__('Can\'t close Request Quote'));
        }

        $quote->setStatus(Status::CANCELED);
        $this->quoteRepository->save($quote);

        return true;
    }

    /**
     * @throws NoSuchEntityException
     * @throws StateException
     * @throws InputException
     */
    public function updateCustomerInfo(int $quoteId, string $email, string $firstName, string $lastName): bool
    {
        if (!$this->isEnabled()) {
            throw new StateException(__('Quote Cart has been disabled.'));
        }

        $quote = $this->quoteRepository->getActive($quoteId);
        if (!$this->isCustomerGroupAllowed(GroupManagement::NOT_LOGGED_IN_ID)) {
            throw new StateException(__('Quote Cart has been disabled for this customer group.'));
        }

        if (!$this->emailAddressValidator->isValid($email)) {
            throw new InputException(__('"%1" is not a valid email address.', $email));
        }

        $quote->setCustomerLastname($lastName);
        $quote->setCustomerFirstname($firstName);
        $quote->setCustomerEmail($email);
        $quote->setCustomerIsGuest(true);

        $this->quoteRepository->save($quote);

        return true;
    }

    /**
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function clear(int $quoteId): bool
    {
        if (!$this->isEnabled()) {
            throw new StateException(__('Quote Cart has been disabled.'));
        }

        $quote = $this->quoteRepository->getActive($quoteId);
        if (!$this->isCustomerGroupAllowed((int)$quote->getCustomer()->getGroupId())) {
            throw new StateException(__('Quote Cart has been disabled for this customer group.'));
        }

        $quote->removeAllItems();
        $quote->collectTotals();
        $this->quoteRepository->save($quote);

        return true;
    }

    /**
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function updateCustomerNote(int $quoteId, string $customerNote): bool
    {
        if (!$this->isEnabled()) {
            throw new StateException(__('Quote Cart has been disabled.'));
        }

        $quote = $this->quoteRepository->getActive($quoteId);
        if (!$this->isCustomerGroupAllowed((int)$quote->getCustomer()->getGroupId())) {
            throw new StateException(__('Quote Cart has been disabled for this customer group.'));
        }

        return $this->quoteRepository->addCustomerNote($quoteId, $customerNote);
    }

    private function createAnonymousCart(int $storeId, int $status): QuoteInterface
    {
        /** @var QuoteInterface $quote */
        $quote = $this->quoteFactory->create();
        $quote->setStoreId($storeId);
        $quote->setCustomerIsGuest(1);
        $quote->setStatus($status);
        $quote->setQuoteCurrencyCode($this->storeManager->getStore()->getBaseCurrencyCode());

        return $quote;
    }

    private function getCustomerQuoteCart(int $customerId, int $storeId, int $newStatus): QuoteInterface
    {
        if ($newStatus === Status::ADMIN_CREATED) {
            return $this->createCustomerQuoteCart($customerId, $storeId, $newStatus);
        }

        try {
            $quote = $this->quoteRepository->getActiveForCustomer($customerId);
        } catch (NoSuchEntityException $e) {
            $quote = $this->createCustomerQuoteCart($customerId, $storeId, $newStatus);
        }

        return $quote;
    }

    private function createCustomerQuoteCart(int $customerId, int $storeId, int $newStatus): QuoteInterface
    {
        $customer = $this->customerRepository->getById($customerId);
        $quote = $this->quoteFactory->create();
        $quote->setStoreId($storeId);
        $quote->setCustomer($customer);
        $quote->setCustomerIsGuest(0);
        $quote->setStatus($newStatus);
        $quote->setQuoteCurrencyCode($this->storeManager->getStore()->getBaseCurrencyCode());

        return $quote;
    }

    private function isEnabled(): bool
    {
        return $this->configProvider->isActive();
    }

    private function isCustomerGroupAllowed(int $customerGroupId): bool
    {
        return in_array($customerGroupId, $this->configProvider->getAllowedCustomerGroups());
    }
}
