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

namespace Magetrend\AbandonedCart\Block\Bar;

use Magento\Framework\App\Request\Http;

/**
 * Abstract Bar Block
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Basic extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    public $request;

    /**
     * General module helper
     *
     * @var \Magetrend\AbandonedCart\Helper\Data
     */
    public $helper;

    /**
     * @var \Magetrend\AbandonedCart\Model\ResourceModel\Rule\Collection|null
     */
    public $rules = null;

    /**
     * @var \Magetrend\AbandonedCart\Model\ResourceModel\Rule\CollectionFactory
     */
    public $ruleCollectionFactory;

    /**
     * @var \Magetrend\AbandonedCart\Model\Rule|null|false
     */
    public $currentBar = null;

    /**
     * @var \Magento\Customer\Model\Session
     */
    public $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    public $checkoutSession;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    public $quoteRepository;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    public $stockRegistry;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    public $cookieManager;

    private $quote;

    /**
     * Basic constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magetrend\AbandonedCart\Helper\Data $helper
     * @param \Magetrend\AbandonedCart\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory
     * @param Http $request
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magetrend\AbandonedCart\Helper\Data $helper,
        \Magetrend\AbandonedCart\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        Http $request,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->customerSession = $customerSession;
        $this->quoteRepository = $quoteRepository;
        $this->stockRegistry = $stockRegistry;
        $this->cookieManager = $cookieManager;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
    }

    public function isActive()
    {
        if (!$this->getQuote()) {
            return false;
        }
        if (!$this->getCurrentBar()) {
            return false;
        }

        return true;
    }

    public function getCurrentBar()
    {
        if ($this->currentBar == null) {
            $this->loadCurrentBar();
        }

        return $this->currentBar;
    }

    public function loadCurrentBar()
    {
        $storeId = $this->getStoreId();
        $rulesCollection = $this->getActiveRules($storeId);
        if ($rulesCollection->getSize() == 0) {
            $this->currentBar = false;
            return false;
        }

        foreach ($rulesCollection as $rule) {
            if ($this->isRuleAvailable($rule)) {
                $this->currentBar = $rule;
                return true;
            }
        }
        $this->currentBar = false;
        return false;
    }

    /**
     * Check if we can use this rule
     * @param \Magetrend\AbandonedCart\Model\Rule $rule
     * @return boolean
     */
    public function isRuleAvailable($rule)
    {
        if (!$this->validateQty($rule)) {
            return false;
        }

        if (!$this->validateCookie($rule)) {
            return false;
        }

        if (!$this->validateEvent($rule)) {
            return false;
        }

        if (!$this->validateDelay($rule)) {
            return false;
        }

        return true;
    }

    /**
     * Find item with low qty
     * @param $rule
     * @return bool
     */
    public function validateQty($rule)
    {
        $itemQty = $rule->getItemQty();
        if ($itemQty == 0) {
            return true;
        }

        $cart = $this->getQuote();
        foreach ($cart->getItems() as $item) {
            $stockItem = $this->stockRegistry->getStockItem($item->getProductId());
            $stockQty = $stockItem->getQty();
            if ($stockQty <= $itemQty) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check is cookie created
     * @param $rule
     * @return bool
     */
    public function validateCookie($rule)
    {
        if ($this->cookieManager->getCookie($rule->getCookieName($this->getQuote()->getId()), false)) {
            return false;
        }

        return true;
    }

    /**
     * Validate events
     * @param $rule
     * @return bool
     */
    public function validateEvent($rule)
    {
        $rule->afterLoad();
        $eventList = $rule->getData('trigger_events');
        /**
         * Only for returning visitor
         */
        if (count($eventList) == 1
            && $eventList[0] == \Magetrend\AbandonedCart\Model\Config\Source\Bar\Event::EVENT_BACK_TO_STORE
            && $this->getData('is_visitor_back', 0) == 0
        ) {
            return false;
        }
        return true;
    }

    /**
     * Validate events
     * @param $rule
     * @return bool
     */
    public function validateDelay($rule)
    {
        $delayTime = $rule->getDelayTime() * 60 * 60;
        $quote = $this->getQuote();
        $lastUpdate = $quote->getUpdatedAt() == '0000-00-00 00:00:00'?$quote->getCreatedAt():$quote->getUpdatedAt();
        $lastUpdate = strtotime($lastUpdate);
        if (time() - $delayTime < $lastUpdate) {
            return false;
        }

        return true;
    }

    public function getActiveRules($storeId = 0)
    {
        if ($this->rules == null) {
            $this->rules = $this->ruleCollectionFactory->create()
                ->addFieldToFilter('is_active', 1)
                ->addStoreIdFilter($storeId)
                ->addFieldToFilter('type', \Magetrend\AbandonedCart\Model\Rule::TYPE_BAR)
                ->addCustomerGroupIdFilter($this->getCustomerGroupId())
                ->setOrder('priority', 'DESC')
                ->setOrder('delay_time', 'ASC');
        }
        return $this->rules;
    }

    public function getCustomerGroupId()
    {
        if ($this->customerSession->isLoggedIn()) {
            return $customerGroup=$this->customerSession->getCustomer()->getGroupId();
        }
        return 0;
    }

    public function getQuote()
    {
        if ($this->quote == null) {
            $quote = $this->checkoutSession->getQuote();
            if (!$quote->getId()) {
                $this->quote = false;
            } else {
                $this->quote = $this->quoteRepository->get($quote->getId());
            }
        }
        return $this->quote;
    }

    public function getColor($key, $default = '')
    {
        $bar = $this->getCurrentBar();
        if (empty($bar->getData($key))) {
            return $default;
        }

        return '#'.str_replace('#', '', $bar->getData($key));
    }

    public function getFontSize($key, $default = '')
    {
        $bar = $this->getCurrentBar();
        if (empty($bar->getData($key))) {
            return $default;
        }

        return ((int)$bar->getData($key)).'px';
    }

    public function getShowAfter()
    {
        $bar = $this->getCurrentBar();
        $showAfter = $bar->getShowAfter();
        $startedAt = $this->getData('started_at', 0) / 1000;
        return $showAfter - (time() - $startedAt);
    }
}
