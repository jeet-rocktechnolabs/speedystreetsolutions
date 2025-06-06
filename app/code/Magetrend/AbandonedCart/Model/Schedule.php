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

namespace Magetrend\AbandonedCart\Model;

/**
 * Schedule model
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Schedule extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var ScheduleFactory
     */
    public $scheduleFactory;

    /**
     * Schedule constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ScheduleFactory $scheduleFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magetrend\AbandonedCart\Model\ScheduleFactory $scheduleFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->scheduleFactory = $scheduleFactory;
        return parent::__construct($context, $registry, $resource, $resourceCollection);
    }

    /**
     * Initialize object popup
     */
    //@codingStandardsIgnoreLine
    protected function _construct()
    {
        $this->_init('Magetrend\AbandonedCart\Model\ResourceModel\Schedule');
    }

    public function updateSchedules($rule, $data)
    {
        if (!isset($data['options'])) {
            return false;
        }
        foreach ($data['options'] as $schedule) {
            if ($schedule['is_delete'] == 1 && $schedule['entity_id'] > 0) {
                $this->deleteSchedule($schedule['entity_id']);
            } elseif ($schedule['entity_id'] > 0) {
                $this->updateSchedule($schedule['entity_id'], $schedule);
            } elseif ($schedule['entity_id'] == 0 && $schedule['is_delete'] != 1) {
                $this->createSchedule($rule, $schedule);
            }
        }
    }

    public function updateSchedule($scheduleId, $data)
    {
        $schedule = $this->scheduleFactory->create()->load($scheduleId);
        if (!$schedule->getId()) {
            return false;
        }

        unset($data['id']);
        unset($data['entity_id']);
        $schedule->addData($data)
            ->save();
        return true;
    }

    /**
     * Delete schedule
     * @param $scheduleId
     * @return bool
     */
    public function deleteSchedule($scheduleId)
    {
        $schedule = $this->scheduleFactory->create();
        $schedule->load($scheduleId);
        if (!$schedule->getId()) {
            return false;
        }
        $schedule->delete();
        return true;
    }

    public function createSchedule($rule, $data)
    {
        $schedule = $this->scheduleFactory->create();
        unset($data['id']);
        unset($data['entity_id']);
        $data['rule_id'] = $rule->getId();
        $schedule->setData($data)
            ->save();
        return true;
    }
}
