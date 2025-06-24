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

namespace Magetrend\AbandonedCart\Model\Cron\Cart;

/**
 * Cart Schedule Manager
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class ScheduleManager extends \Magetrend\AbandonedCart\Model\Cron\ScheduleManagerAbstract
{
    /**
     * Process schedules by store id
     * @param $storeId
     * @return bool
     */
    public function runByStore($storeId)
    {
        if (!$this->moduleHelper->isActive($storeId)) {
            return false;
        }

        $collection = $this->getActiveRules();
        if ($collection->getSize() == 0) {
            return false;
        }

        $ruleCollection = $this->applyStoreFilter($collection, $storeId);
        if (empty($ruleCollection)) {
            return false;
        }

        $quoteCollection = $this->getQuoteCollection($storeId);
        if ($quoteCollection->getTotalCount() == 0) {
            return false;
        }

        foreach ($quoteCollection->getItems() as $quote) {
            $this->processQuote($quote, $ruleCollection);
        }

        return true;
    }

    /**
     * Returns quote collection
     * @param int $storeId
     * @return \Magento\Quote\Api\Data\CartSearchResultsInterface
     */
    public function getQuoteCollection($storeId = 0)
    {
        $before = date('Y-m-d H:i:s', strtotime('-6 hours'));
        $createdAtFilter = $this->filterBuilder
            ->setField('created_at')
            ->setValue($before)
            ->setConditionType('gteq')
            ->create();

        $updatedAtFilter = $this->filterBuilder
            ->setField('updated_at')
            ->setValue($before)
            ->setConditionType('gteq')
            ->create();

        $search = $this->searchCriteriaBuilder
            ->addFilter('store_id', $storeId)
            ->addFilter('ac_status', 0)
            ->addFilters([
                $createdAtFilter, $updatedAtFilter
            ])
            ->addFilter('reserved_order_id', true, 'null')
            ->create();

        $quoteCollection = $this->quoteRepository->getList($search);
        return $quoteCollection;
    }

    /**
     * Apply rule on quote
     * @param $quote
     * @param $ruleCollection
     */
    public function processQuote($quote, $ruleCollection)
    {
        foreach ($ruleCollection as $rule) {
            if ($rule->getType() != \Magetrend\AbandonedCart\Model\Rule::TYPE_ABANDONED_CART) {
                continue;
            }

            if (!$this->canApplyRule($rule, $quote)) {
                continue;
            }

            $this->applyRule($quote, $rule);
            break;
        }

        $quote->setData('ac_status', 1)
            ->save();
    }

    /**
     * Apply rule on quote
     *
     * @param $quote
     * @param $rule
     * @return bool
     */
    public function applyRule($quote, $rule)
    {
        $scheduleCollection = $this->getScheduleCollection($rule->getId());
        if (empty($scheduleCollection)) {
            return false;
        }

        $groupHash = $this->random->getUniqueHash();
        foreach ($scheduleCollection as $schedule) {
            $sendAt = ($schedule->getDelayMinute() * 60);
            $sendAt += ($schedule->getDelayHour() * 60 * 60);
            $sendAt += ($schedule->getDelayDay() * 60 * 60 * 24);
            $countFrom = $quote->getUpdatedAt() == '0000-00-00 00:00:00'?$quote->getCreatedAt():$quote->getUpdatedAt();
            $sendAt = date('Y-m-d H:i:s', strtotime($countFrom) + $sendAt);

            $this->queueFactory->create()
                ->setData(
                    [
                        'group_hash' => $groupHash,
                        'store_id' => $quote->getStoreId(),
                        'quote_id' => $quote->getId(),
                        'schedule_id' => $schedule->getId(),
                        'rule_id' => $rule->getId(),
                        'created_at' => date('Y-m-d H:i:s'),
                        'scheduled_at' => $sendAt,
                        'status' => \Magetrend\AbandonedCart\Model\Queue::STATUS_NEW
                    ]
                )
                ->save();
        }
    }

    /**
     * Check can rule be applied
     *
     * @param \Magetrend\AbandonedCart\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool
     */
    public function canApplyRule($rule, $quote)
    {
        $availableForGroup = $rule->getData('customer_groups');
        if (strpos($availableForGroup, ',-1,') === false
            && strpos($availableForGroup, ','.$quote->getCustomerGroupId().',') === false) {
            return false;
        }

        $items = $quote->getAllItems();
        if (empty($items)) {
            return false;
        }

        foreach ($items as $item) {
            if ($rule->validate($item->getAddress())) {
                return true;
            }
        }

        return false;
    }

    public function prepareQueueData($object)
    {
        return [
            'quote_id' => $object->getId(),
            'store_id' => $object->getStoreId(),
        ];
    }
}
