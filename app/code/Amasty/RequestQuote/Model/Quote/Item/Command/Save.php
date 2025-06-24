<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote\Item\Command;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Api\Data\QuoteItemInterface;
use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Amasty\RequestQuote\Model\Quote\Frontend\UpdateQuoteItems;
use Amasty\RequestQuote\Model\Quote\Frontend\UpdateQuoteItems\UpdateRequestedPrice;
use Amasty\RequestQuote\Model\Quote\Item\ConvertCartItem;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item\CartItemPersister;

class Save implements SaveInterface
{
    /**
     * @var QuoteRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var CartItemPersister
     */
    private $cartItemPersister;

    /**
     * @var ConvertCartItem
     */
    private $convertCartItem;

    /**
     * @var UpdateQuoteItems
     */
    private $updateQuoteItems;

    /**
     * @var UpdateRequestedPrice
     */
    private $updateRequestedPrice;

    public function __construct(
        QuoteRepositoryInterface $quoteRepository,
        CartItemPersister $cartItemPersister,
        ConvertCartItem $convertCartItem,
        UpdateQuoteItems $updateQuoteItems,
        UpdateRequestedPrice $updateRequestedPrice
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->cartItemPersister = $cartItemPersister;
        $this->convertCartItem = $convertCartItem;
        $this->updateQuoteItems = $updateQuoteItems;
        $this->updateRequestedPrice = $updateRequestedPrice;
    }

    /**
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     */
    public function save(QuoteItemInterface $quoteItem): QuoteItemInterface
    {
        /** @var QuoteInterface $quote */
        $quote = $this->quoteRepository->get((int)$quoteItem->getQuoteId());

        $updateData = [
            'price' => $quoteItem->getPrice(),
            'qty' => $quoteItem->getQty(),
            'note' => $quoteItem->getCustomerNote()
        ];

        if (!$quoteItem->getItemId()) {
            // trigger manual item add, because we need initialized item before call $this->updateQuoteItems->execute
            $quoteItem = $this->cartItemPersister->save($quote, $quoteItem);
            $updateData['quote_item'] = $quoteItem;
            $lastAddedItem = $quoteItem;
        } else {
            $quoteItems = $quote->getItems();
            $quoteItems[] = $quoteItem;
            $quote->setItems($quoteItems);
            $this->updateRequestDataWithExistingItem($updateData, $quote, $quoteItem);
        }

        $this->updateQuoteItems->execute($quote, [(int)$quoteItem->getItemId() => $updateData]);
        $this->updateRequestedPrice->execute([$quoteItem]);

        $this->quoteRepository->save($quote);

        if (isset($lastAddedItem)) {
            // correct last added item for case manual adding product
            $quote->setLastAddedItem($lastAddedItem);
        }

        return $this->convertCartItem->execute($quote->getLastAddedItem());
    }

    /**
     * Do not loss custom price or customer note , if not passed by update request.
     */
    private function updateRequestDataWithExistingItem(
        array &$requestData,
        QuoteInterface $quote,
        QuoteItemInterface $quoteItem
    ): void {
        if ($existingItem = $quote->getItemById($quoteItem->getItemId())) {
            $existingItem = $this->convertCartItem->execute($existingItem);
            if (!$quoteItem->getPrice()) {
                $requestData['price'] = $existingItem->getPrice();
            }
            if (!$quoteItem->getCustomerNote()) {
                $requestData['note'] = $existingItem->getCustomerNote();
            }
        }
    }
}
