<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Api\Data\CustomerAccount;

interface QuoteInterface extends \Amasty\RequestQuote\Api\Data\QuoteInterface
{
    /**
     * @return string|null
     */
    public function getQuoteAdminNote(): ?string;

    /**
     * @param string $adminNote
     * @return void
     */
    public function setQuoteAdminNote(string $adminNote): void;

    /**
     * @param string $adminDiscountNote
     * @return void
     */
    public function setQuoteAdminDiscountNote(string $adminDiscountNote): void;

    /**
     * @return string|null
     */
    public function getQuoteAdminDiscountNote(): ?string;

    /**
     * @return \Amasty\RequestQuote\Api\Data\CustomerAccount\QuoteItemInterface[]
     */
    public function getItems();

    /**
     * @param \Amasty\RequestQuote\Api\Data\CustomerAccount\QuoteItemInterface[]|null $items
     * @return \Amasty\RequestQuote\Api\Data\CustomerAccount\QuoteInterface
     */
    public function setItems(array $items = null);
}
