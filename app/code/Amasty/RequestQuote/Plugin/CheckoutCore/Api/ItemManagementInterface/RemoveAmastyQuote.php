<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Plugin\CheckoutCore\Api\ItemManagementInterface;

use Amasty\CheckoutCore\Api\ItemManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class RemoveAmastyQuote
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    public function __construct(CartRepositoryInterface $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    /**
     * @param ItemManagementInterface $subject
     * @param int $cartId
     * @param int $itemId
     */
    public function beforeRemove(ItemManagementInterface $subject, $cartId, $itemId): void
    {
        $quote = $this->cartRepository->get($cartId);
        $quoteItem = $quote->getItemById($itemId);

        if ($quoteItem && $quoteItem->getId() && $this->isAmastyQuoteItem($quoteItem)) {
            foreach ($quote->getAllVisibleItems() as $quoteItem) {
                if ($this->isAmastyQuoteItem($quoteItem)) {
                    $quoteItem->removeOption('amasty_quote_price');
                    $quote->deleteItem($quoteItem);
                }
            }
            $this->cartRepository->save($quote);
        }
    }

    private function isAmastyQuoteItem(QuoteItem $quoteItem): bool
    {
        return (bool) $quoteItem->getOptionByCode('amasty_quote_price');
    }
}
