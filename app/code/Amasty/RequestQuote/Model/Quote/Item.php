<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote;

use Amasty\RequestQuote\Api\Data\QuoteItemInterface;

class Item extends \Magento\Quote\Model\Quote\Item implements QuoteItemInterface
{
    public function getCustomerNote(): ?string
    {
        return $this->getData(QuoteItemInterface::CUSTOMER_NOTE_KEY);
    }

    public function setCustomerNote(string $customerNote): void
    {
        $this->setData(QuoteItemInterface::CUSTOMER_NOTE_KEY, $customerNote);
    }
}
