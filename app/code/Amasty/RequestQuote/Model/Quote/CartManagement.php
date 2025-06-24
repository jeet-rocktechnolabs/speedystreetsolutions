<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote;

use Amasty\RequestQuote\Api\CartManagementInterface;
use Amasty\RequestQuote\Model\ConfigProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class CartManagement implements CartManagementInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    public function __construct(ConfigProvider $configProvider, CartRepositoryInterface $cartRepository)
    {
        $this->configProvider = $configProvider;
        $this->cartRepository = $cartRepository;
    }

    /**
     * @throws StateException
     * @throws NoSuchEntityException
     */
    public function removeQuoteFromCart(int $cartId): bool
    {
        if (!$this->isEnabled()) {
            throw new StateException(__('Quote Cart has been disabled.'));
        }

        $cart = $this->cartRepository->getActive($cartId);
        if (!$this->isCustomerGroupAllowed((int)$cart->getCustomer()->getGroupId())) {
            throw new StateException(__('Quote Cart has been disabled for this customer group.'));
        }

        foreach ($cart->getAllVisibleItems() as $quoteItem) {
            if ($this->isAmastyQuoteItem($quoteItem)) {
                $quoteItem->removeOption('amasty_quote_price');
                $cart->deleteItem($quoteItem);
            }
        }

        $this->cartRepository->save($cart);

        return true;
    }

    private function isEnabled(): bool
    {
        return $this->configProvider->isActive();
    }

    private function isCustomerGroupAllowed(int $customerGroupId): bool
    {
        return in_array($customerGroupId, $this->configProvider->getAllowedCustomerGroups());
    }

    private function isAmastyQuoteItem(QuoteItem $quoteItem): bool
    {
        return (bool)$quoteItem->getOptionByCode('amasty_quote_price');
    }
}
