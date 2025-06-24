<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Api\Data;

use Magento\Quote\Api\Data\CartItemInterface;

interface QuoteItemInterface extends CartItemInterface
{
    public const ADMIN_NOTE_KEY = 'admin_note';
    public const CUSTOMER_NOTE_KEY = 'customer_note';
    public const REQUESTED_PRICE = 'requested_price';
    public const CUSTOM_PRICE = 'requested_custom_price';
    public const HIDE_ORIGINAL_PRICE = 'hide_original_price';

    /**
     * @return string|null
     */
    public function getCustomerNote(): ?string;

    /**
     * @param string $customerNote
     * @return void
     */
    public function setCustomerNote(string $customerNote): void;
}
