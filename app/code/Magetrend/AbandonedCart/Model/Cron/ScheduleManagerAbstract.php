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

namespace Magetrend\AbandonedCart\Model\Cron;

/**
 * Abstract Schedule Manager
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
abstract class ScheduleManagerAbstract
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \Magetrend\AbandonedCart\Model\ResourceModel\Schedule\CollectionFactory
     */
    public $scheduleCollectionFactory;

    /**
     * @var null
     */
    public $ruleCollection = null;

    /**
     * @var \Magetrend\AbandonedCart\Model\ResourceModel\Rule\CollectionFactory
     */
    public $ruleCollectionFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    public $quoteRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    public $searchCriteriaBuilder;

    /**
     * @var
     */
    public $filterBuilder;

    /**
     * @var \Magetrend\AbandonedCart\Model\QueueFactory
     */
    public $queueFactory;

    /**
     * @var array
     */
    private $scheduleCollection = [];

    public $orderRepository;

    public $random;

    /**
     * @var \Magetrend\AbandonedCart\Helper\Data
     */
    public $moduleHelper;

    public $emulation;

    /**
     * ScheduleManager constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magetrend\AbandonedCart\Model\ResourceModel\Schedule\CollectionFactory $scheduleCollectionFactory
     * @param \Magetrend\AbandonedCart\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory
     * @param \Magetrend\AbandonedCart\Model\QueueFactory $queueFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magetrend\AbandonedCart\Model\ResourceModel\Schedule\CollectionFactory $scheduleCollectionFactory,
        \Magetrend\AbandonedCart\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        \Magetrend\AbandonedCart\Model\QueueFactory $queueFactory,
        \Magetrend\AbandonedCart\Helper\Data $moduleHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Math\Random $random,
        \Magento\Store\Model\App\Emulation $emulation
    ) {
        $this->storeManager = $storeManager;
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->quoteRepository = $quoteRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->queueFactory = $queueFactory;
        $this->orderRepository = $orderRepository;
        $this->filterBuilder = $filterBuilder;
        $this->random = $random;
        $this->moduleHelper = $moduleHelper;
        $this->emulation = $emulation;
    }

    /**
     * Process schedules by store id
     * @param $storeId
     * @return bool
     */
    abstract public function runByStore($storeId);

    /**
     * Prepare data related on object
     * @param $object
     * @return mixed
     */
    abstract public function prepareQueueData($object);

    /**
     * Process schedules
     */
    public function run()
    {
        if (!$this->moduleHelper->isActive()) {
            return;
        }

        if ($this->storeManager->isSingleStoreMode()) {
            $this->runByStore(0);
            $this->runByStore(1);
        } else {
            foreach ($this->storeManager->getStores() as $store) {
                $this->emulation->startEnvironmentEmulation(
                    $store->getId(),
                    \Magento\Framework\App\Area::AREA_FRONTEND,
                    true
                );
                $this->runByStore($store->getId());
                $this->emulation->stopEnvironmentEmulation();
            }
        }
    }

    /**
     * @param $rule
     * @param $quote
     * @return bool
     */
    public function canApplyRule($rule, $quote)
    {
        if (!$this->validateCustomerGroup($rule, $quote->getCustomerGroupId())) {
            return false;
        }

        return $this->validateConditions($rule, $quote->getAllItems());
    }

    /**
     * Check can rule be applied
     *
     * @param \Magetrend\AbandonedCart\Model\Rule $rule
     * @param $items
     * @return bool
     */
    public function validateConditions($rule, $items)
    {
        foreach ($items as $item) {
            if ($rule->validate($item->getAddress())) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns schedules
     * @param $ruleId
     * @return mixed
     */
    public function getScheduleCollection($ruleId)
    {
        if (!isset($this->scheduleCollection[$ruleId])) {
            $this->scheduleCollection[$ruleId] = $schedules = $this->scheduleCollectionFactory->create()
                ->addFieldToFilter('rule_id', $ruleId);
        }
        return $this->scheduleCollection[$ruleId];
    }

    /**
     * Filter item by store id
     * @param $collection
     * @param int $storeId
     * @return array
     */
    public function applyStoreFilter($collection, $storeId = 0)
    {
        if ($this->storeManager->isSingleStoreMode()) {
            return $collection;
        }

        $ruleCollection = [];
        foreach ($collection as $rule) {
            $storeIds = $rule->getStoreIds();
            if (strpos($storeIds, ',0,') !== false || strpos($storeIds, ','.$storeId.',') !== false) {
                $ruleCollection[] = $rule;
            }
        }
        return $ruleCollection;
    }

    /**
     * Returns active rules collection
     * @return null
     */
    public function getActiveRules()
    {
        if ($this->ruleCollection === null) {
            $this->ruleCollection = $this->ruleCollectionFactory->create()
                ->addFieldToFilter('is_active', 1)
                ->setOrder('priority', 'DESC');
        }
        return $this->ruleCollection;
    }

    /**
     * Can apply a rule for customer group
     * @param $rule
     * @param $customerGroup
     * @return bool
     */
    public function validateCustomerGroup($rule, $customerGroup)
    {
        $availableForGroup = $rule->getData('customer_groups');
        if (strpos($availableForGroup, ',-1,') === false
            && strpos($availableForGroup, ','.$customerGroup.',') === false) {
            return false;
        }
        return true;
    }

    /**
     * Apply rule on quote
     *
     * @param $object
     * @param $rule
     * @return bool
     */
    public function applyRule($object, $rule)
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
            $sendAt = date('Y-m-d H:i:s', strtotime($object->getCreatedAt()) + $sendAt);

            $data = array_merge(
                $this->prepareQueueData($object),
                [
                    'group_hash' => $groupHash,
                    'schedule_id' => $schedule->getId(),
                    'rule_id' => $rule->getId(),
                    'created_at' => date('Y-m-d H:i:s'),
                    'scheduled_at' => $sendAt,
                    'status' => \Magetrend\AbandonedCart\Model\Queue::STATUS_NEW
                ]
            );
            $this->queueFactory->create()
                ->setData($data)
                ->save();
        }
    }
}
