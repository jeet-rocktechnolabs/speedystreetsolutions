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

namespace Magetrend\AbandonedCart\Model\Manager;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Cart/Order restore manager class
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Restore
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    public $quoteRepository;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    public $orderRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    public $searchCriteriaBuilder;

    /**
     * @var CustomerSession
     */
    public $checkoutSession;

    /**
     * @var CustomerSession
     */
    public $customerSession;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    public $customerFactory;

    public $queueFactory;

    public $queue;

    public $eventManager;

    /**
     * Cart constructor.
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magetrend\AbandonedCart\Model\QueueFactory $queueFactory,
        \Magetrend\AbandonedCart\Model\Queue $queue,
        \Magento\Framework\Event\Manager $eventManager
    ) {
        $this->quoteRepository = $cartRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->orderRepository = $orderRepository;
        $this->customerFactory = $customerFactory;
        $this->queueFactory = $queueFactory;
        $this->queue = $queue;
        $this->eventManager = $eventManager;
    }

    public function restoreCart($quid)
    {
        $quote = $this->getQuote($quid);
        if (!$quote) {
            return false;
        }

        $quote->setData('reserved_order_id', null)
            ->save();
        $this->checkoutSession->setQuoteId($quote->getId());
        return true;
    }

    public function getQuote($hash)
    {
        $search = $this->searchCriteriaBuilder
            ->addFilter('ac_hash', $hash)
            ->create();

        $quoteList = $this->quoteRepository->getList($search);
        if ($quoteList->getTotalCount() == 0) {
            return false;
        }

        foreach ($quoteList->getItems() as $quote) {
            if (!$quote->getIsActive()) {
                $quote->setIsActive(true);
                $this->quoteRepository->save($quote);
            }
            return $quote;
        }
    }

    public function restoreOrder($orderHash)
    {
        $order = $this->getOrder($orderHash);
        if (!$order) {
            return false;
        }

        $quote = $this->quoteRepository->get($order->getQuoteId());
        $quote->setData('reserved_order_id', null);
        if (!$quote->getIsActive()) {
            $quote->setIsActive(true);
        }

        $this->quoteRepository->save($quote);

        $this->checkoutSession->setQuoteId($quote->getId());
        if ($order->getCustomerId() > 0) {
            $customer = $this->customerFactory->create()->load($order->getCustomerId());
            $this->customerSession->setCustomerAsLoggedIn($customer);
        }

        return true;
    }

    public function getOrder($hash)
    {
        $search = $this->searchCriteriaBuilder
            ->addFilter('ac_hash', $hash)
            ->create();

        $orderList = $this->orderRepository->getList($search);
        if ($orderList->getTotalCount() == 0) {
            return false;
        }

        foreach ($orderList->getItems() as $order) {
            return $order;
        }
    }

    public function logEvent($msgId, $status, $cancelOther = false)
    {
        if (!is_numeric($msgId)) {
            return false;
        }
        /**
         * @var \Magetrend\AbandonedCart\Model\Queue $msg
         */
        $msg = $this->queueFactory->create()->load($msgId);
        if (!$msg->getId()) {
            return false;
        }

        $msg->updateStatus($status);
        if ($cancelOther) {
            $this->eventManager->dispatch('mtac_cart_restored', ['queue' => $msg]);
        }
        return true;
    }
}
