<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote\Item;

use Amasty\RequestQuote\Api\Data\QuoteItemInterface;
use Amasty\RequestQuote\Api\QuoteItemManagementInterface;
use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Amasty\RequestQuote\Model\ConfigProvider;
use Amasty\RequestQuote\Model\Quote\Item\Command\Save as SaveQuoteItemCommand;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

class ItemManagement implements QuoteItemManagementInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var QuoteRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var SaveQuoteItemCommand
     */
    private $saveQuoteItemCommand;

    public function __construct(
        ConfigProvider $configProvider,
        QuoteRepositoryInterface $quoteRepository,
        SaveQuoteItemCommand $saveQuoteItemCommand
    ) {
        $this->configProvider = $configProvider;
        $this->quoteRepository = $quoteRepository;
        $this->saveQuoteItemCommand = $saveQuoteItemCommand;
    }

    /**
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function save(QuoteItemInterface $quoteItem): QuoteItemInterface
    {
        if (!$this->isEnabled()) {
            throw new StateException(__('Quote Cart has been disabled.'));
        }

        $quoteId = (int)$quoteItem->getQuoteId();
        if (!$quoteId) {
            throw new InputException(
                __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'quoteId'])
            );
        }

        $quote = $this->quoteRepository->get($quoteId);
        if (!$this->isCustomerGroupAllowed((int)$quote->getCustomer()->getGroupId())) {
            throw new StateException(__('Quote Cart has been disabled for this customer group.'));
        }

        return $this->saveQuoteItemCommand->save($quoteItem);
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
