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
 * Send emails cron class
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Send
{
    /**
     * @var \Magetrend\AbandonedCart\Helper\Data
     */
    public $helper;

    /**
     * @var \Magetrend\AbandonedCart\Model\Cron\QueueManager
     */
    public $queueManager;

    /**
     * Send constructor.
     * @param \Magetrend\AbandonedCart\Helper\Data $helper
     * @param \Magetrend\AbandonedCart\Model\Cron\QueueManager $queueManager
     */
    public function __construct(
        \Magetrend\AbandonedCart\Helper\Data $helper,
        \Magetrend\AbandonedCart\Model\Cron\QueueManager $queueManager
    ) {
        $this->helper = $helper;
        $this->queueManager = $queueManager;
    }

    /**
     * Send scheduled emails
     * @return bool
     */
    public function execute()
    {
        $this->queueManager->sendScheduledEmails();
        return true;
    }
}
