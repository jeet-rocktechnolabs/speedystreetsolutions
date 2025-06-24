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

use Magento\Quote\Model\Quote\Address;
use Magento\Rule\Model\AbstractModel;

/**
 * Rule model
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Rule extends AbstractModel
{
    const TYPE_ABANDONED_CART = 'abandoned_cart';

    const TYPE_FOLLOW_UP = 'follow_up';

    const TYPE_BAR = 'bar';

    public $multiSelectOptions = [
        'store_ids', 'customer_groups', 'trigger_events', 'cancel_events', 'payment_methods'
    ];

    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\CombineFactory
     */
    public $condCombineFactory;

    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory
     */
    public $condProdCombineF;

    /**
     * Store already validated addresses and validation results
     *
     * @var array
     */
    public $validatedAddresses = [];

    /**
     * @var array
     */
    private $scheduleData = [];

    /**
     * @var Schedule
     */
    public $schedule;

    /**
     * @var ResourceModel\Schedule\CollectionFactory
     */
    public $scheduleCollectionFactory;

    /**
     * @var null
     */
    private $scheduleCollection = null;

    /**
     * Rule constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\SalesRule\Model\Rule\Condition\CombineFactory $condCombineFactory
     * @param \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineF
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\SalesRule\Model\Rule\Condition\CombineFactory $condCombineFactory,
        \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineF,
        \Magetrend\AbandonedCart\Model\Schedule $schedule,
        \Magetrend\AbandonedCart\Model\ResourceModel\Schedule\CollectionFactory $scheduleCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->condCombineFactory = $condCombineFactory;
        $this->condProdCombineF = $condProdCombineF;
        $this->schedule = $schedule;
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }
    /**
     * Initialize object popup
     */
    //@codingStandardsIgnoreLine
    protected function _construct()
    {
        $this->_init('Magetrend\AbandonedCart\Model\ResourceModel\Rule');
    }

    /**
     * Get rule condition combine model instance
     *
     * @return \Magento\SalesRule\Model\Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->condCombineFactory->create();
    }

    /**
     * Get rule condition product combine model instance
     *
     * @return \Magento\SalesRule\Model\Rule\Condition\Product\Combine
     */
    public function getActionsInstance()
    {
        return $this->condProdCombineF->create();
    }

    /**
     * Check cached validation result for specific address
     *
     * @param Address $address
     * @return bool
     */
    public function hasIsValidForAddress($address)
    {
        $addressId = $this->_getAddressId($address);
        return isset($this->validatedAddresses[$addressId]) ? true : false;
    }

    /**
     * Set validation result for specific address to results cache
     *
     * @param Address $address
     * @param bool $validationResult
     * @return $this
     */
    public function setIsValidForAddress($address, $validationResult)
    {
        $addressId = $this->_getAddressId($address);
        $this->validatedAddresses[$addressId] = $validationResult;
        return $this;
    }

    /**
     * Get cached validation result for specific address
     *
     * @param Address $address
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsValidForAddress($address)
    {
        $addressId = $this->_getAddressId($address);
        return isset($this->validatedAddresses[$addressId]) ? $this->validatedAddresses[$addressId] : false;
    }

    public function beforeSave()
    {
        foreach ($this->multiSelectOptions as $option) {
            if (is_array($this->getData($option))) {
                $this->setData($option, ','.implode(',', $this->getData($option)).',');
            }
        }

        if (is_array($this->getData('field'))) {
            $this->scheduleData = $this->getData('field');
            $this->unsetData('field');
        }

        return parent::beforeSave();
    }

    public function afterSave()
    {
        if (!empty($this->scheduleData)) {
            $this->schedule->updateSchedules($this, $this->scheduleData);
        }
        return parent::afterSave();
    }

    protected function _afterLoad()
    {
        foreach ($this->multiSelectOptions as $option) {
            if (!is_array($this->getData($option)) && !empty($this->getData($option))) {
                $options = explode(',', trim(rtrim($this->getData($option), ','), ','));
                $this->setData($option, $options);
            }
        }
        return parent::_afterLoad();
    }

    /**
     * Return id for address
     *
     * @param Address $address
     * @return string
     */
    private function _getAddressId($address)
    {
        if ($address instanceof Address) {
            return $address->getId();
        }
        return $address;
    }

    public function getSchedules()
    {
        if ($this->scheduleCollection === null) {
            $this->scheduleCollection = $this->scheduleCollectionFactory->create()
                ->addFieldToFilter('rule_id', $this->getId());
        }

        return $this->scheduleCollection;
    }

    public function getCookieName($quoteId = '')
    {
        return 'mtacbar'.$this->getId().'_'.$quoteId;
    }
}
