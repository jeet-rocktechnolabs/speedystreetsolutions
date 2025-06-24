<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CustomerNotificationTemplates implements OptionSourceInterface
{
    public const SUBMITTED_QUOTE = 0;
    public const APPROVED_QUOTE = 1;
    public const MODIFIED_QUOTE = 2;
    public const CANCELED_QUOTE = 3;
    public const EXPIRED_QUOTE = 4;
    public const REMINDER = 5;

    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::SUBMITTED_QUOTE,
                'label' => __('Submitted Quote')
            ],
            [
                'value' => self::APPROVED_QUOTE,
                'label' => __('Approved Quote')
            ],
            [
                'value' => self::MODIFIED_QUOTE,
                'label' => __('Modified Quote')
            ],
            [
                'value' => self::CANCELED_QUOTE,
                'label' => __('Canceled Quote')
            ],
            [
                'value' => self::EXPIRED_QUOTE,
                'label' => __('Expired Quote')
            ],
            [
                'value' => self::REMINDER,
                'label' => __('Reminder')
            ]
        ];
    }
}
