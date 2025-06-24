<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model;

use Amasty\Base\Model\ConfigProviderAbstract;

/**
 * @api
 */
class ConfigProvider extends ConfigProviderAbstract
{
    /**
     * xpath prefix of module (section)
     * @var string '{section}/'
     */
    protected $pathPrefix = 'amasty_request_quote/';

    private const IS_ACTIVE_PATH = 'general/is_active';
    private const EXCLUDED_CATEGORIES_PATH = 'general/exclude_category';
    private const ALLOWED_CUSTOMER_GROUP_PATH = 'general/visible_for_groups';
    private const ALLOW_CREATE_ACCOUNT_FOR_GUEST_PATH = 'general/allow_create_account_for_guest';
    private const ENABLE_CUSTOMER_NOTIFICATIONS = 'customer_notifications/enable_notifications';
    private const DISABLED_CUSTOMER_NOTIFICATIONS = 'customer_notifications/disable_notifications_for';
    public const CONFIG_PATH_CUSTOMER_SUBMIT_EMAIL
        = 'amasty_request_quote/customer_notifications/customer_template_submit';
    public const CONFIG_PATH_CUSTOMER_APPROVE_EMAIL
        = 'amasty_request_quote/customer_notifications/customer_template_approve';
    public const CONFIG_PATH_CUSTOMER_EDIT_EMAIL
        = 'amasty_request_quote/customer_notifications/customer_template_edit_quote';
    public const CONFIG_PATH_CUSTOMER_CANCEL_EMAIL
        = 'amasty_request_quote/customer_notifications/customer_template_cancel';
    public const CONFIG_PATH_CUSTOMER_EXPIRED_EMAIL
        = 'amasty_request_quote/customer_notifications/customer_template_expired';
    public const CONFIG_PATH_CUSTOMER_REMINDER_EMAIL
        = 'amasty_request_quote/customer_notifications/customer_template_reminder';

    public const URL_KEY_PATH = 'general/url_key';

    public function isActive(): bool
    {
        return $this->isSetFlag(self::IS_ACTIVE_PATH);
    }

    public function getExcludeCategories(): array
    {
        return explode(',', (string) $this->getValue(self::EXCLUDED_CATEGORIES_PATH));
    }

    public function getAllowedCustomerGroups(): array
    {
        return explode(',', (string) $this->getValue(self::ALLOWED_CUSTOMER_GROUP_PATH));
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isCustomerNotificationsEnabled(?int $storeId = null): bool
    {
        return (bool)$this->getValue(self::ENABLE_CUSTOMER_NOTIFICATIONS, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getDisabledCustomerNotifications(?int $storeId = null): string
    {
        return (string)$this->getValue(self::DISABLED_CUSTOMER_NOTIFICATIONS, $storeId);
    }

    /**
     * Is extension are enabled.
     */
    public function isEnabled(): bool
    {
        return $this->isSetFlag('general/is_active');
    }

    /**
     * Get URL key of the extension for storefront request.
     */
    public function getRouteName(?int $storeId = null): ?string
    {
        return $this->getValue(self::URL_KEY_PATH, $storeId);
    }

    public function isAllowedCreateAccountForGuest(?int $storeId = null): bool
    {
        return $this->isSetFlag(self::ALLOW_CREATE_ACCOUNT_FOR_GUEST_PATH, $storeId);
    }
}
