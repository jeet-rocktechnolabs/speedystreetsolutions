<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote\CustomerAccount;

use Amasty\RequestQuote\Api\Data\CustomerAccount\QuoteItemInterface;

class QuoteItem extends \Amasty\RequestQuote\Model\Quote\Item implements QuoteItemInterface
{
    public function getAdminNote(): ?string
    {
        return $this->getData(QuoteItemInterface::ADMIN_NOTE_KEY);
    }

    public function setAdminNote(string $adminNote): void
    {
        $this->setData(QuoteItemInterface::ADMIN_NOTE_KEY, $adminNote);
    }
}
