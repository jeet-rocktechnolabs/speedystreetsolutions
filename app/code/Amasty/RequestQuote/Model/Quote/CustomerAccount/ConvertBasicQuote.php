<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote\CustomerAccount;

use Amasty\RequestQuote\Api\Data\CustomerAccount\QuoteInterface;
use Amasty\RequestQuote\Api\Data\CustomerAccount\QuoteInterfaceFactory;
use Amasty\RequestQuote\Api\Data\QuoteInterface as BasicQuoteInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

/**
 * Convert RequestQuote Basic Quote to Quote for present in customer account.
 */
class ConvertBasicQuote
{
    /**
     * @var QuoteInterfaceFactory
     */
    private $quoteFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var JsonSerializer
     */
    private $jsonSerializer;

    /**
     * @var ConvertCartItem
     */
    private $convertCartItem;

    public function __construct(
        QuoteInterfaceFactory $quoteFactory,
        DataObjectHelper $dataObjectHelper,
        JsonSerializer $jsonSerializer,
        ConvertCartItem $convertCartItem
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->jsonSerializer = $jsonSerializer;
        $this->convertCartItem = $convertCartItem;
    }

    public function execute(BasicQuoteInterface $basicQuote): QuoteInterface
    {
        /** @var QuoteInterface $quote */
        $quote = $this->quoteFactory->create();

        $this->dataObjectHelper->mergeDataObjects(BasicQuoteInterface::class, $quote, $basicQuote);

        $additionalData = $basicQuote->getRemarks()
            ? $this->jsonSerializer->unserialize($basicQuote->getRemarks())
            : [];
        if (isset($additionalData[BasicQuoteInterface::CUSTOMER_NOTE_KEY])) {
            $quote->setQuoteCustomerNote($additionalData[BasicQuoteInterface::CUSTOMER_NOTE_KEY]);
        }
        if (isset($additionalData[BasicQuoteInterface::ADMIN_NOTE_KEY])) {
            $quote->setQuoteAdminDiscountNote($additionalData[BasicQuoteInterface::ADMIN_NOTE_KEY]);
        }
        if (isset($additionalData[BasicQuoteInterface::ADMIN_NOTE_REMARK_KEY])) {
            $quote->setQuoteAdminNote($additionalData[BasicQuoteInterface::ADMIN_NOTE_REMARK_KEY]);
        }

        $items = [];
        foreach ($basicQuote->getItems() as $cartItem) {
            $items[] = $this->convertCartItem->execute($cartItem);
        }
        $quote->setItems($items);

        return $quote;
    }
}
