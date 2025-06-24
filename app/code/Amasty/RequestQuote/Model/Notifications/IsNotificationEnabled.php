<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Notifications;

use Amasty\RequestQuote\Model\ConfigProvider;

class IsNotificationEnabled
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    /**
     * @param int $notificationId
     * @return bool
     */
    public function execute(int $notificationId): bool
    {
        $isEnabled = $this->configProvider->isCustomerNotificationsEnabled();
        $disabledNotifications = $this->configProvider->getDisabledCustomerNotifications();

        if ($isEnabled && $disabledNotifications !== '') {
            $disabledNotifications = explode(',', $disabledNotifications);

            $isEnabled = !in_array($notificationId, $disabledNotifications);
        }

        return $isEnabled;
    }
}
