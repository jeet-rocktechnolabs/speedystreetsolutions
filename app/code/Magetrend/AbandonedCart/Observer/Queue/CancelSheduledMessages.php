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

namespace Magetrend\AbandonedCart\Observer\Queue;

use Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer;

/**
 * Cancel scheduled messages
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class CancelSheduledMessages implements ObserverInterface
{
    /**
     * @var \Magetrend\AbandonedCart\Model\ResourceModel\Queue\CollectionFactory
     */
    public $queueCollectionFactory;

    /**
     * @var \Magetrend\AbandonedCart\Model\RuleFactory
     */
    public $ruleFactory;

    /**
     * CancelSheduledMessages constructor.
     * @param \Magetrend\AbandonedCart\Model\ResourceModel\Queue\CollectionFactory $queueCollectionFactory
     * @param \Magetrend\AbandonedCart\Model\RuleFactory $ruleFactory
     */
    public function __construct(
        \Magetrend\AbandonedCart\Model\ResourceModel\Queue\CollectionFactory $queueCollectionFactory,
        \Magetrend\AbandonedCart\Model\RuleFactory $ruleFactory
    ) {
        $this->queueCollectionFactory = $queueCollectionFactory;
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * Hook for customer login event
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(Observer $observer)
    {
        $message = $observer->getQueue();
        $messageCollection = $this->queueCollectionFactory->create()
            ->addFieldToFilter('group_hash', $message->getGroupHash())
            ->addFieldToFilter('status', \Magetrend\AbandonedCart\Model\Queue::STATUS_NEW);

        if ($messageCollection->getSize() == 0) {
            return true;
        }

        foreach ($messageCollection as $message) {
            $rule = $this->ruleFactory->create()->load($message->getRuleId());
            if (!$rule || !$rule->getId()) {
                continue;
            }

            if (!in_array('link_clicked',  $rule->getCancelEvents())) {
                continue;
            }

            /**
             * @var \Magetrend\AbandonedCart\Model\Queue $message
             */
            $message->updateStatus(\Magetrend\AbandonedCart\Model\Queue::STATUS_CANCELED);
        }

        return true;
    }
}
