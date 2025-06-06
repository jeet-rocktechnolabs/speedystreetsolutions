<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Api\Data\CustomerAccount;

interface QuoteItemInterface extends \Amasty\RequestQuote\Api\Data\QuoteItemInterface
{
    /**
     * @return string|null
     */
    public function getAdminNote(): ?string;

    /**
     * @param string $adminNote
     * @return void
     */
    public function setAdminNote(string $adminNote): void;
}
