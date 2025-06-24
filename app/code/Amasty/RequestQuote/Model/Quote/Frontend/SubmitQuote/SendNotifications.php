<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote\Frontend\SubmitQuote;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Helper\Data as ConfigHelper;
use Amasty\RequestQuote\Model\Email\AdminNotification;
use Amasty\RequestQuote\Model\Email\CustomerNotification;

class SendNotifications
{
    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var CustomerNotification
     */
    private $customerNotification;

    /**
     * @var AdminNotification
     */
    private $adminNotification;

    public function __construct(
        ConfigHelper $configHelper,
        CustomerNotification $customerNotification,
        AdminNotification $adminNotification
    ) {
        $this->configHelper = $configHelper;
        $this->customerNotification = $customerNotification;
        $this->adminNotification = $adminNotification;
    }

    public function execute(QuoteInterface $quote): void
    {
        $this->customerNotification->execute($quote);
        if ($this->configHelper->isAdminNotificationsInstantly()) {
            $this->adminNotification->sendNotification((int)$quote->getId());
        }
    }
}
