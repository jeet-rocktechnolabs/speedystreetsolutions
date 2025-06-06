<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.3 or later
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */

namespace Magetrend\AbandonedCart\Cron;

/**
 * Create schedules cron class
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Schedule
{
    /**
     * @var \Magetrend\AbandonedCart\Helper\Data
     */
    public $helper;

    /**
     * @var \Magetrend\AbandonedCart\Model\Cron\Order\ScheduleManager
     */
    public $orderScheduleManager;

    /**
     * @var \Magetrend\AbandonedCart\Model\Cron\Cart\ScheduleManager
     */
    public $cartScheduleManager;

    /**
     * Schedule constructor.
     * @param \Magetrend\AbandonedCart\Helper\Data $helper
     * @param \Magetrend\AbandonedCart\Model\Cron\Order\ScheduleManager $orderScheduleManager
     * @param \Magetrend\AbandonedCart\Model\Cron\Cart\ScheduleManager $cartScheduleManager
     */
    public function __construct(
        \Magetrend\AbandonedCart\Helper\Data $helper,
        \Magetrend\AbandonedCart\Model\Cron\Order\ScheduleManager $orderScheduleManager,
        \Magetrend\AbandonedCart\Model\Cron\Cart\ScheduleManager $cartScheduleManager
    ) {
        $this->helper = $helper;
        $this->orderScheduleManager = $orderScheduleManager;
        $this->cartScheduleManager = $cartScheduleManager;
    }

    /**
     * Method triggered by cron
     * Execute if delay time is set longer than 0
     * Will send delayed coupons
     * @return bool
     */
    public function execute()
    {
        $this->cartScheduleManager->run();
        $this->orderScheduleManager->run();
        return true;
    }
}
