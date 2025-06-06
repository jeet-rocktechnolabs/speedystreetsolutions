<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote\CustomerAccount;

use Amasty\RequestQuote\Api\Data\CustomerAccount\QuoteItemInterface;
use Amasty\RequestQuote\Api\Data\CustomerAccount\QuoteItemInterfaceFactory;
use Amasty\RequestQuote\Api\Data\QuoteItemInterface as BasicQuoteItemInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Convert magento cart item to RequestQuote Quote Item for present in customer account.
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
     * @var JsonSerializer
     */
    private $jsonSerializer;

    public function __construct(
        QuoteItemInterfaceFactory $quoteItemFactory,
        DataObjectHelper $dataObjectHelper,
        JsonSerializer $jsonSerializer
    ) {
        $this->quoteItemFactory = $quoteItemFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->jsonSerializer = $jsonSerializer;
    }

    public function execute(CartItemInterface $cartItem): QuoteItemInterface
    {
        /** @var QuoteItemInterface $quoteItem */
        $quoteItem = $this->quoteItemFactory->create();
        $quoteItem->setProduct($cartItem->getProduct());

        $this->dataObjectHelper->mergeDataObjects(CartItemInterface::class, $quoteItem, $cartItem);

        $additionalData = $cartItem->getAdditionalData()
            ? $this->jsonSerializer->unserialize($cartItem->getAdditionalData())
            : [];
        if (isset($additionalData[BasicQuoteItemInterface::CUSTOMER_NOTE_KEY])) {
            $quoteItem->setCustomerNote($additionalData[BasicQuoteItemInterface::CUSTOMER_NOTE_KEY]);
        }
        if (isset($additionalData[BasicQuoteItemInterface::ADMIN_NOTE_KEY])) {
            $quoteItem->setAdminNote($additionalData[BasicQuoteItemInterface::ADMIN_NOTE_KEY]);
        }

        return $quoteItem;
    }
}
