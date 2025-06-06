<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote\Item;

use Amasty\RequestQuote\Api\Data\QuoteItemInterface;
use Amasty\RequestQuote\Api\Data\QuoteItemInterfaceFactory;
use Amasty\RequestQuote\Helper\Cart as CartHelper;
use Amasty\RequestQuote\Model\Quote\Item as QuoteItem;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item as CartItem;

/**
 * Convert magento cart item to RequestQuote quote item.
 */
class ConvertCartItem
{
    /**
     * @var QuoteItemInterfaceFactory
     */
    private $quoteItemFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var CartHelper
     */
    private $cartHelper;

    public function __construct(
        QuoteItemInterfaceFactory $quoteItemFactory,
        DataObjectHelper $dataObjectHelper,
        CartHelper $cartHelper
    ) {
        $this->quoteItemFactory = $quoteItemFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->cartHelper = $cartHelper;
    }

    public function execute(CartItem $cartItem): QuoteItemInterface
    {
        /** @var QuoteItemInterface|QuoteItem $quoteItem */
        $quoteItem = $this->quoteItemFactory->create();
        $quoteItem->setProduct($cartItem->getProduct());

        $this->dataObjectHelper->mergeDataObjects(CartItemInterface::class, $quoteItem, $cartItem);

        $customerNote = $this->cartHelper->retrieveCustomerNote($cartItem->getAdditionalData());
        $quoteItem->setCustomerNote($customerNote);

        return $quoteItem;
    }
}
