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

use Magento\Quote\Model\Quote;
use Magento\SalesRule\Model\RuleRepository;
use Magetrend\AbandonedCart\Model\ResourceModel\Queue\Collection;
use Magetrend\AbandonedCart\Model\Rule;

/**
 * Queue model
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Queue extends \Magento\Framework\Model\AbstractModel
{
    const STATUS_NEW = 'new';

    const STATUS_SENT = 'send';

    const STATUS_SEND_FAILED = 'sent_failed';

    const STATUS_SEND_FAILED_NO_EMAIL = 'no_email_address';

    const STATUS_ORDER_WAS_PLACED = 'order_was_placed';

    const STATUS_ORDER_NOT_FOUND = 'order_not_found';

    const STATUS_CART_NOT_FOUND = 'cart_not_found';

    const STATUS_ORDER_WAS_PAID = 'order_was_paid';

    const STATUS_ANOTHER_ORDER_WAS_PLACED = 'another_order_was_placed';

    const STATUS_ANOTHER_CART_WAS_CREATED = 'another_cart_was_crated';

    const STATUS_CANCELED = 'canceled';

    const STATUS_ORDER_RESTORED = 'order_restored';

    const STATUS_CART_RESTORED = 'cart_restored';

    const STATUS_PRODUCT_OUT_OF_STOCK = 'product_out_of_stock';

    const STATUS_ALL_OUT_OF_STOCK = 'all_product_out_of_stock';

    const STATUS_MISSED_TO_SEND = 'missed_to_send';

    const STATUS_SENDING = 'sending';

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    public $quoteRepository;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    public $orderRepository;

    /**
     * @var ScheduleFactory
     */
    public $scheduleFactory;

    /**
     * @var RuleFactory
     */
    public $ruleFactory;

    /**
     * @var RuleRepository
     */
    public $ruleRepository;

    /**
     * @var \Magento\SalesRule\Model\Coupon\Massgenerator
     */
    public $massGenerator;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    public $salesRuleFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $date;

    /**
     * @var VisitorFactory
     */
    public $visitorFactory;

    /**
     * @var Mail\Template\TransportBuilder
     */
    public $transportBuilder;

    /**
     * @var \Magento\Framework\Api\SortOrderBuilder
     */
    public $sortOrderBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    public $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    public $filterBuilder;

    /**
     * @var \Magetrend\AbandonedCart\Helper\Data
     */
    public $moduleHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \Magento\Framework\UrlInterface $url
     */
    public $url;

    public $eventManager;

    public $queueCollectionFactory;

    public $rule = null;

    public $quote = null;

    public $order = null;

    public $email = null;

    public $quoteFactory;

    public $couponCollectionFactory;

    /**
     * Queue constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param ScheduleFactory $scheduleFactory
     * @param RuleFactory $ruleFactory
     * @param VisitorFactory $visitorFactory
     * @param ResourceModel\Queue\CollectionFactory $queueCollectionFactory
     * @param \Magetrend\AbandonedCart\Helper\Data $moduleHelper
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\SalesRule\Model\RuleFactory $salesRuleFactory
     * @param RuleRepository $ruleRepository
     * @param \Magento\SalesRule\Model\Coupon\Massgenerator $massGenerator
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Framework\Event\Manager $eventManager
     * @param \Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory $couponCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     *
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magetrend\AbandonedCart\Model\ScheduleFactory $scheduleFactory,
        \Magetrend\AbandonedCart\Model\RuleFactory $ruleFactory,
        \Magetrend\AbandonedCart\Model\VisitorFactory $visitorFactory,
        \Magetrend\AbandonedCart\Model\ResourceModel\Queue\CollectionFactory $queueCollectionFactory,
        \Magetrend\AbandonedCart\Helper\Data $moduleHelper,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\SalesRule\Model\RuleFactory $salesRuleFactory,
        \Magento\SalesRule\Model\RuleRepository $ruleRepository,
        \Magento\SalesRule\Model\Coupon\Massgenerator $massGenerator,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\UrlInterface $url,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory $couponCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->orderRepository = $orderRepository;
        $this->scheduleFactory = $scheduleFactory;
        $this->ruleRepository = $ruleRepository;
        $this->massGenerator = $massGenerator;
        $this->ruleFactory = $ruleFactory;
        $this->salesRuleFactory = $salesRuleFactory;
        $this->date = $date;
        $this->visitorFactory = $visitorFactory;
        $this->moduleHelper = $moduleHelper;
        $this->transportBuilder = $transportBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->url = $url;
        $this->storeManager = $storeManagerInterface;
        $this->queueCollectionFactory = $queueCollectionFactory;
        $this->eventManager = $eventManager;
        $this->quoteFactory = $quoteFactory;
        $this->couponCollectionFactory = $couponCollectionFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize object popup
     */
    //@codingStandardsIgnoreLine
    protected function _construct()
    {
        $this->_init('Magetrend\AbandonedCart\Model\ResourceModel\Queue');
    }

    /**
     * Process queue record
     * @return bool
     */
    public function send()
    {
        $this->updateStatus(self::STATUS_SENDING);

        $schedule = $this->getSchedule();
        if (!$schedule->getId()) {
            $this->updateStatus(self::STATUS_SEND_FAILED);
            return false;
        }

        $scheduledAt = strtotime($this->getData('scheduled_at'));

        if ((time() - $scheduledAt) > 60*60*2) {
            $this->updateStatus(self::STATUS_MISSED_TO_SEND);
            return false;
        }

        $emailAddress = $this->getEmail();
        if (empty($emailAddress)) {
            $this->updateStatus(self::STATUS_SEND_FAILED_NO_EMAIL);
            return false;
        }

        $rule = $this->getRule();
        $this->beforeSend($rule);

        if (!$this->canSend()) {
            return false;
        }

        $templateData = $this->getTemplateData($rule, $schedule);
        $this->sendEmail($emailAddress, $templateData, $schedule);
        $this->setSentAt($this->date->gmtDate());
        $this->updateStatus(self::STATUS_SENT);
    }

    public function getTemplateData($rule, $schedule)
    {
        $templateData = [];
        if ($rule->getType() == \Magetrend\AbandonedCart\Model\Rule::TYPE_FOLLOW_UP) {
            $order = $this->getOrder();
            $storeId = $order->getStoreId();
            $templateData = [
                'items' => $order->getItems(),
                'order' => $order,
                'view_order_link' => $this->getOrderViewLink($order),
                'restore_order_link' => $this->getOrderRestoreLink($order)
            ];
        }

        if ($rule->getType() == \Magetrend\AbandonedCart\Model\Rule::TYPE_ABANDONED_CART) {
            $quote = $this->getQuote();
            $storeId = $quote->getStoreId();
            $templateData = [
                'items' => $quote->getItems(),
                'quote' => $quote,
                'restore_cart_link' => $this->getQuoteRestoreLink($quote)
            ];

            $coupon = false;
            if ($schedule->getSalesRuleId() > 0) {
                $coupon = $this->getCoupon($schedule->getSalesRuleId());
            }

            if ($coupon) {
                $templateData['coupon'] = $coupon;
                $templateData['coupon_code'] = $coupon->getCode();
                $templateData['coupon_expire_date'] = $this->moduleHelper->formatDateTime(
                    $coupon->getData('expiration_date'),
                    $storeId
                );
            }
        }

        return $templateData;
    }

    public function getEmail()
    {
        if ($this->email == null) {
            $quote = $this->getQuote();
            $emailAddress = $quote->getCustomerEmail();
            if (empty($emailAddress)) {
                $emailAddress = $this->getVisitorEmailByHash($quote->getVisitorHash());
            }
            $this->email = $emailAddress;
        }
        return $this->email;
    }

    /**
     * Update queue record status
     * @param $status
     */
    public function updateStatus($status)
    {
        $this->setStatus($status)->save();
    }

    /**
     * Returns coupon code
     *
     * @param $salesRuleId
     * @return bool|mixed|string
     */
    public function getCoupon($salesRuleId)
    {
        if (!$this->getId() || !is_numeric($salesRuleId)) {
            return false;
        }

        if ($discountCode = $this->getDiscountCodeByHash()) {
            //return $discountCode;
        }

        $salesRule = $this->salesRuleFactory->create()
            ->load($salesRuleId);

        if (!$salesRule->getRuleId()) {
            return false;
        }

        if ($salesRule->getData('use_auto_generation') == 0) {
            return false;
        }

        $aCartRule = $this->getRule();
        $codeGenerator = $this->massGenerator;
        $codeGenerator->setData('qty', 1);
        $codeGenerator->setData('rule_id', $salesRule->getRuleId());
        $codeGenerator->setData('length', $aCartRule->getCouponLength());
        $codeGenerator->setData('format', $aCartRule->getCouponFormat());
        $codeGenerator->setData('prefix', $aCartRule->getCouponPrefix());
        $codeGenerator->setData('suffix', $aCartRule->getCouponSuffix());
        $codeGenerator->setData('dash', $aCartRule->getCouponDash());

        $codeGenerator->setData('uses_per_coupon', 1);
        $codeGenerator->setData('uses_per_customer', 1);

        $codeGenerator->generatePool();
        $latestCoupon = max($salesRule->getCoupons());

        $expireInDays = $aCartRule->getData('coupon_expire_in_days');
        if (is_numeric($expireInDays) && $expireInDays > 0) {
            $latestCoupon->setData(
                'expiration_date',
                $this->date->gmtDate('Y-m-d H:i:s', time() + 3600 * 24 * $expireInDays)
            );
        }
        $latestCoupon->setData('ac_group_hash', $this->getGroupHash())
            ->save();

        return $latestCoupon;
    }

    public function getDiscountCodeByHash()
    {
        $hash = $this->getGroupHash();
        $collection = $this->couponCollectionFactory->create()
            ->addFieldToFilter('ac_group_hash', $hash);

        if ($collection->getSize() == 0) {
            return false;
        }

        return $collection->getFirstItem();
    }

    /**
     * Returns rule
     * @return mixed
     */
    public function getRule()
    {
        if ($this->rule == null) {
            $ruleId = $this->getRuleId();
            $this->rule = $this->ruleFactory->create();
            $this->rule->load($ruleId);
        }

        return $this->rule;
    }

    /**
     * Returns schedule
     * @return mixed
     */
    public function getSchedule()
    {
        $schedule = $this->scheduleFactory->create();
        $schedule->load($this->getScheduleId());
        return $schedule;
    }

    /**
     * Returns quote
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function getQuote()
    {
        if ($this->quote == null) {
            $quoteId = $this->getQuoteId();
            if (!is_numeric($quoteId) || $quoteId <= 0) {
                $this->updateStatus(self::STATUS_CART_NOT_FOUND);
                $this->quote = false;
                return false;
            }

            $this->quote = $this->quoteRepository->get($quoteId);
            if (!$this->quote->getId()) {
                $this->updateStatus(self::STATUS_CART_NOT_FOUND);
                $this->quote = false;
                return false;
            }
        }
        return $this->quote;
    }

    /**
     * Returns order
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function getOrder()
    {
        if ($this->order == null) {
            $orderId = $this->getOrderId();
            if (!is_numeric($orderId) || $orderId <= 0) {
                $this->updateStatus(self::STATUS_ORDER_NOT_FOUND);
                $this->order = false;
                return false;
            }

            $this->order = $this->orderRepository->get($orderId);
            if (!$this->order->getId()) {
                $this->updateStatus(self::STATUS_ORDER_NOT_FOUND);
                $this->order = false;
                return false;
            }
        }
        return $this->order;
    }

    /**
     * Send Email
     * @param $emailAddress
     * @param $quote
     * @param $coupon
     * @param $schedule
     * @return bool
     */
    public function sendEmail($emailAddress, $templateData, $schedule)
    {
if(!empty($emailAddress) && filter_var($emailAddress, FILTER_VALIDATE_EMAIL)){
        $templateId = $schedule->getEmailTemplate();
        $storeId = $this->getStoreId();
        $fromEmail = $this->moduleHelper->getSendFrom($storeId, 'email');
        $fromName = $this->moduleHelper->getSendFrom($storeId, 'name');
        $bcc_templateData = $templateData;
        $templateData = array_merge($this->moduleHelper->getDefaultEmailVariables($storeId), $templateData);

        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId])
            ->setTemplateVars($templateData)
            ->setFrom([
                'name' => $fromName,
                'email' => $fromEmail
            ])
            ->addTo($emailAddress)
            ->setReplyTo($fromEmail, $fromName)
            ->getTransport();

        $transport->sendMessage();

        $bcc_templateData['bcc_details'] = 'Customer email:'.$emailAddress;
        $bcc_templateData = array_merge($this->moduleHelper->getDefaultEmailVariables($storeId), $bcc_templateData);

        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId])
            ->setTemplateVars($bcc_templateData)
            ->setFrom([
                'name' => $fromName,
                'email' => $fromEmail
            ])
            ->addTo('sales@speedystreetsolutions.com')
            ->setReplyTo($fromEmail, $fromName)
            ->getTransport();

        $transport->sendMessage();
        return true;
}
    }

    /**
     * Returns cart restore link
     * @return string
     */
    public function getQuoteRestoreLink($quote)
    {
        $this->createHash($quote);
        return $this->url->getUrl('acart/restore/cart', ['qid' => $quote->getAcHash(), 'msg' => $this->getId()]);
    }

    /**
     * Returns cart restore link
     * @return string
     */
    public function getOrderRestoreLink($order)
    {
        $this->createHash($order);
        return $this->url->getUrl('acart/restore/order', ['oid' => $order->getAcHash(), 'msg' => $this->getId()]);
    }

    public function getOrderViewLink($order)
    {
        if ($order->getCustomerId()) {
            return $this->storeManager->getStore($order->getStoreId())
                ->getUrl('sales/order/view', ['order_id' => $order->getId()]);
        }
        return $this->storeManager->getStore($order->getStoreId())
            ->getUrl('sales/guest/form');
    }

    public function createHash($dataObject)
    {
        if (empty($dataObject->getAcHash())) {
            $hash = hash('md5', $dataObject->getId().'_'.time().'_'.rand(0, 9999));
            $dataObject->setAcHash($hash)
                ->save();
        }
    }

    /**
     * Return visitor email by hash
     * @param $hash
     * @return string
     */
    public function getVisitorEmailByHash($hash)
    {
        $visitor = $this->visitorFactory->create();
        $visitor->load($hash, 'hash');
        if ($visitor->getId()) {
            return $visitor->getEmail();
        }
        return '';
    }

    public function beforeSend($rule)
    {
        $schedule = $this->getSchedule();
        if ($rule->getType() == Rule::TYPE_FOLLOW_UP) {
            $this->eventManager->dispatch(
                'mtac_order_before_send',
                [
                    'queue' => $this,
                    'rule' => $rule,
                    'schedule' => $schedule,
                    'order' => $this->getOrder()
                ]
            );
        } elseif ($rule->getType() == Rule::TYPE_ABANDONED_CART) {
            $quote = $this->getQuote();
            $this->eventManager->dispatch(
                'mtac_cart_before_send',
                [
                    'queue' => $this,
                    'rule' => $rule,
                    'schedule' => $schedule,
                    'quote' => $quote
                ]
            );
        }
        return true;
    }

    public function canSend()
    {
        return $this->getStatus() == self::STATUS_NEW || $this->getStatus() == self::STATUS_SENDING;
    }
}
