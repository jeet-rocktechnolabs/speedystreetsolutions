<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote\Backend\Edit;

use Amasty\RequestQuote\Helper\Data;
use Magento\Framework\Stdlib\DateTime\DateTime;

class DateUtils
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        Data $helper,
        DateTime $dateTime
    ) {
        $this->helper = $helper;
        $this->dateTime = $dateTime;
    }

    /**
     * @param string|null $oldExpireDate
     * @param string|null $newExpireDate
     * @return string|null
     */
    public function getExpiredDate($oldExpireDate, $newExpireDate)
    {
        return $this->helper->getExpirationTime() !== null
            ? $this->getValidatedDate($oldExpireDate, $newExpireDate)
            : null;
    }

    /**
     * @param string|null $oldReminderDate
     * @param string|null $newReminderDate
     * @return string|null
     */
    public function getReminderDate($oldReminderDate, $newReminderDate)
    {
        return $this->helper->getReminderTime() !== null
            ? $this->getValidatedDate($oldReminderDate, $newReminderDate)
            : null;
    }

    /**
     * @param string|null $oldDate
     * @param string|null $newDate
     * @return string|null
     */
    private function getValidatedDate($oldDate, $newDate)
    {
        $result = null;
        if ($newDate
            && $this->dateTime->gmtDate('y-m-d', $newDate)
            && $this->isDateChanged($oldDate, $newDate)
        ) {
            $result = $this->dateTime->gmtDate(null, $newDate);
        }

        return $result;
    }

    /**
     * @param string|null $currentDate
     * @param string $newDate
     *
     * @return bool
     */
    private function isDateChanged($currentDate, string $newDate)
    {
        return !$currentDate || $this->dateTime->gmtDate('y-m-d', $newDate)
            != $this->dateTime->gmtDate('y-m-d', $currentDate);
    }
}
