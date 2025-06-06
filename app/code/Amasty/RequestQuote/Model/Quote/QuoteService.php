<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote;

use Amasty\RequestQuote\Api\QuoteManagementInterface;
use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Amasty\RequestQuote\Api\QuoteServiceInterface;
use Amasty\RequestQuote\Model\ConfigProvider;
use Amasty\RequestQuote\Model\Email\Sender as EmailSender;
use Amasty\RequestQuote\Model\Quote\Frontend\SubmitQuote as SubmitQuoteService;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

class QuoteService implements QuoteServiceInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var QuoteManagementInterface
     */
    private $quoteManagement;

    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @var QuoteRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var SubmitQuoteService
     */
    private $submitQuoteService;

    public function __construct(
        ConfigProvider $configProvider,
        QuoteManagementInterface $quoteManagement,
        EmailSender $emailSender,
        QuoteRepositoryInterface $quoteRepository,
        SubmitQuoteService $submitQuoteService
    ) {
        $this->configProvider = $configProvider;
        $this->quoteManagement = $quoteManagement;
        $this->emailSender = $emailSender;
        $this->quoteRepository = $quoteRepository;
        $this->submitQuoteService = $submitQuoteService;
    }

    public function cancelQuote(int $quoteId, ?int $customerId = null): bool
    {
        if ($this->quoteManagement->cancelQuote($quoteId, $customerId)) {
            $this->emailSender->sendDeclineEmail($this->quoteRepository->get($quoteId));
            return true;
        }

        return false;
    }

    /**
     * @throws StateException
     * @throws NoSuchEntityException
     */
    public function submit(int $quoteId): string
    {
        if (!$this->isEnabled()) {
            throw new StateException(__('Quote Cart has been disabled.'));
        }

        $quote = $this->quoteRepository->getActive($quoteId);
        if (!$this->isCustomerGroupAllowed((int)$quote->getCustomer()->getGroupId())) {
            throw new StateException(__('Quote Cart has been disabled for this customer group.'));
        }

        if (!$quote->getAllVisibleItems()) {
            throw new StateException(__('Empty Quote Cart can not be submitted.'));
        }

        if ($quote->getCustomerIsGuest() || !$quote->getCustomer()->getId()) {
            if ($this->configProvider->isAllowedCreateAccountForGuest()) {
                throw new StateException(__('Customer account should to be created to submit the quote.'));
            } elseif (!$quote->getCustomerEmail()
                || !$quote->getCustomerFirstname()
                || !$quote->getCustomerLastname()
            ) {
                throw new StateException(__('Provide the user data to submit the quote.'));
            }
        }

        $quote->collectTotals();
        $this->submitQuoteService->execute($quote);

        return $quote->getIncrementId();
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
