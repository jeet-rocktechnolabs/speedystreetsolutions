<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Api\Data;

use Magento\Quote\Api\Data\CartExtensionInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * @method setRemarks
 */
interface QuoteInterface extends CartInterface
{
    public const MAIN_TABLE = 'amasty_quote';

    public const STATUS = 'status';
    public const EXPIRED_DATE = 'expired_date';
    public const REMINDER_DATE = 'reminder_date';
    public const ADMIN_NOTIFICATION_SEND = 'admin_notification_send';
    public const ADMIN_NOTE_KEY = 'admin_note';
    public const ADMIN_NOTE_REMARK_KEY = 'admin_note_remark';
    public const CUSTOMER_NOTE_KEY = 'customer_note';
    public const QUOTE_CUSTOMER_NOTE_KEY = 'quote_customer_note';
    public const DISCOUNT = 'discount';
    public const SURCHARGE = 'surcharge';
    public const REMINDER_SEND = 'reminder_send';
    public const SUBMITED_DATE = 'submited_date';
    public const SHIPPING_CAN_BE_MODIFIED = 'shipping_can_modified';
    public const SHIPPING_CONFIGURE = 'shipping_configured';
    public const CUSTOM_FEE = 'custom_fee';
    public const CUSTOM_METHOD_ENABLED = 'custom_method_enabled';
    public const SUM_ORIGINAL_PRICE = 'sum_original_price';
    public const CUSTOMER_EMAIL = 'customer_email';
    public const CUSTOMER_LASTNAME = 'customer_lastname';
    public const CUSTOMER_FIRSTNAME = 'customer_firstname';

    /**
     * @return int
     */
    public function getStatus(): int;

    /**
     * @param int $status
     * @return void
     */
    public function setStatus(int $status): void;

    /**
     * @return bool
     */
    public function isShippingConfigure(): bool;

    /**
     * @param bool $shippingConfigure
     * @return void
     */
    public function setShippingConfigure(bool $shippingConfigure): void;

    /**
     * @return string|null
     */
    public function getCustomerFirstname(): ?string;

    /**
     * @return string|null
     */
    public function getCustomerLastname(): ?string;

    /**
     * @return string|null
     */
    public function getCustomerEmail(): ?string;

    /**
     * @return string|null
     */
    public function getQuoteCustomerNote(): ?string;

    /**
     * Using this method not update DB after save.
     * @see QuoteInterface::setRemarks
     *
     * @param string $customerNote
     * @return void
     */
    public function setQuoteCustomerNote(string $customerNote): void;

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Amasty\RequestQuote\Api\Data\QuoteExtensionInterface|CartExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Amasty\RequestQuote\Api\Data\QuoteExtensionInterface|CartExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(CartExtensionInterface $extensionAttributes);
}
