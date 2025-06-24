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
 * Queue manager
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class QueueManager
{
    /**
     * @var \Magetrend\AbandonedCart\Model\ResourceModel\Queue\CollectionFactory
     */
    public $queueCollectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $date;

    /**
     * @var \Magetrend\AbandonedCart\Helper\Data
     */
    public $moduleHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    public $emulation;

    /**
     * QueueManager constructor.
     * @param \Magetrend\AbandonedCart\Model\ResourceModel\Queue\CollectionFactory $queueCollectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magetrend\AbandonedCart\Helper\Data $moduleHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magetrend\AbandonedCart\Model\ResourceModel\Queue\CollectionFactory $queueCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magetrend\AbandonedCart\Helper\Data $moduleHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\App\Emulation $emulation
    ) {
        $this->queueCollectionFactory = $queueCollectionFactory;
        $this->date = $date;
        $this->scopeConfig = $scopeConfig;
        $this->moduleHelper = $moduleHelper;
        $this->storeManager = $storeManager;
        $this->emulation = $emulation;
    }

    public function sendScheduledEmails()
    {
        $dataFormat = 'Y-m-d H:i:s';
        $size = $this->scopeConfig->getValue(
            'abandonedcart/cron/limit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            0
        );

        if (!$size || !is_numeric($size)) {
            $size = 1;
        }

        $collection = $this->queueCollectionFactory->create()
            ->addFieldTofilter('scheduled_at', ['gteq' => $this->date->gmtDate($dataFormat, time() - 3600 * 100)])
            ->addFieldTofilter('scheduled_at', ['lteq' => $this->date->gmtDate($dataFormat)])
            ->addFieldToFilter('status', \Magetrend\AbandonedCart\Model\Queue::STATUS_NEW)
            ->setPageSize($size)
            ->setCurPage(1);

        if ($collection->getSize() == 0) {
            return true;
        }

        foreach ($collection as $queue) {
            $this->emulation->startEnvironmentEmulation(
                $queue->getStoreId(),
                \Magento\Framework\App\Area::AREA_FRONTEND,
                true
            );
            $queue->send();
            $this->emulation->stopEnvironmentEmulation();
        }

        return true;
    }

}
