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

namespace Magetrend\AbandonedCart\Model\Cron\Order;

/**
 * Order Schedule Manager
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

        $collection = $this->getActiveRules(\Magetrend\AbandonedCart\Model\Rule::TYPE_FOLLOW_UP);
        if ($collection->getSize() == 0) {
            return false;
        }

        $ruleCollection = $this->applyStoreFilter($collection, $storeId);
        if (empty($ruleCollection)) {
            return false;
        }

        $orderCollection = $this->getOrderCollection($storeId);
        if ($orderCollection->getTotalCount() == 0) {
            return false;
        }

        foreach ($orderCollection->getItems() as $order) {
            $this->processOrder($order, $ruleCollection);
        }

        return true;
    }

    /**
     * Apply rule on quote
     * @param $order
     * @param $ruleCollection
     */
    public function processOrder($order, $ruleCollection)
    {
        foreach ($ruleCollection as $rule) {
            $quote = $this->quoteRepository->get($order->getQuoteId());
            if ($rule->getType() != \Magetrend\AbandonedCart\Model\Rule::TYPE_FOLLOW_UP) {
                continue;
            }

            if (!$this->canApplyRule($rule, $quote)) {
                continue;
            }
            $this->applyRule($order, $rule);
            break;
        }
        $order->setData('ac_status', 1)
            ->save();
    }

    /**
     * Returns quote collection
     * @param int $storeId
     * @return \Magento\Quote\Api\Data\CartSearchResultsInterface
     */
    public function getOrderCollection($storeId = 0)
    {
        $search = $this->searchCriteriaBuilder
            ->addFilter('store_id', $storeId)
            ->addFilter('ac_status', 0)
            ->addFilter('total_invoiced', true, 'null')
            ->addFilter('state', 'complete', 'neq')
            ->addFilter('created_at', date('Y-m-d H:i:s', strtotime('-6 hours')), 'gteq')
            ->create();

        $orderCollection = $this->orderRepository->getList($search);
        return $orderCollection;
    }

    /**
     * Add data related on order
     * @param $object
     * @return array
     */
    public function prepareQueueData($object)
    {
        return [
            'order_id' => $object->getId(),
            'store_id' => $object->getStoreId(),
            'quote_id' => $object->getQuoteId(),
        ];
    }
}
