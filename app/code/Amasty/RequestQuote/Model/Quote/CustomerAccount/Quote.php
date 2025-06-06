<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote\CustomerAccount;

use Amasty\RequestQuote\Api\Data\CustomerAccount\QuoteInterface;

class Quote extends \Amasty\RequestQuote\Model\Quote implements QuoteInterface
{
    public function setQuoteAdminNote(string $adminNote): void
    {
        $this->setData(QuoteInterface::ADMIN_NOTE_REMARK_KEY, $adminNote);
    }

    public function getQuoteAdminNote(): ?string
    {
        return $this->getData(QuoteInterface::ADMIN_NOTE_REMARK_KEY);
    }

    public function setQuoteAdminDiscountNote(string $adminDiscountNote): void
    {
        $this->setData(QuoteInterface::ADMIN_NOTE_KEY, $adminDiscountNote);
    }

    public function getQuoteAdminDiscountNote(): ?string
    {
        return $this->getData(QuoteInterface::ADMIN_NOTE_KEY);
    }
}
