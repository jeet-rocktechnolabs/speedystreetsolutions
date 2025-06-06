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

use \Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Visitor model
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Visitor extends \Magento\Framework\Model\AbstractModel
{
    const COOKIE_NAME = 'mtace';

    /**
     * @var \Magento\Customer\Model\Session
     */
    public $customerSession;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    public $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    public $cookieMetadataFactory;

    /**
     * @var ResourceModel\Visitor\CollectionFactory
     */
    public $collectionFactory;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    public $remoteAddress;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    public $orderRepository;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    public $cartRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    public $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\SortOrderBuilder
     */
    public $sortOrderBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    public $filterBuilder;

    /**
     * @var \Magento\Framework\Math\Random
     */
    public $random;

    /**
     * @var CheckoutSession
     */
    public $checkoutSession;

    /**
     * Email constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magetrend\AbandonedCart\Model\ResourceModel\Visitor\CollectionFactory $collectionFactory,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Framework\Math\Random $random,
        CheckoutSession $checkoutSession,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieManager = $cookieManager;
        $this->collectionFactory = $collectionFactory;
        $this->remoteAddress = $remoteAddress;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->cartRepository = $cartRepository;
        $this->filterBuilder = $filterBuilder;
        $this->random = $random;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }
    /**
     * Initialize object popup
     */
    //@codingStandardsIgnoreLine
    protected function _construct()
    {
        $this->_init('Magetrend\AbandonedCart\Model\ResourceModel\Visitor');
    }

    /**
     * Collect Email Address
     *
     * @param $trust
     * @param $email
     * @return bool
     */
    public function collectEmail($email, $trust = 0)
    {
        $this->loadByCookie();
        if (!$this->getId()) {
            return $this->createVisitor($email, $trust);
        }

        if ($this->getTrust() <=  $trust) {
            $this->updateVisitor($email, $trust);
        }

        return false;
    }

    /**
     * Create visitor
     * @param $email
     * @param int $trust
     */
    public function createVisitor($email, $trust = 0)
    {
        $ip = $this->remoteAddress->getRemoteAddress();
        $date = date('Y-m-d H:i:s');
        $hash = $this->random->getUniqueHash();
        $this->setEmail($email)
            ->setHash($hash)
            ->setIp($ip)
            ->setCreatedAt($date)
            ->setUpdatedAt($date)
            ->setTrust($trust)
            ->save();

        $this->updateCookie($hash);
        $this->updateCart($hash);
    }

    /**
     * Update visitor
     *
     * @param $email
     * @param int $trust
     */
    public function updateVisitor($email, $trust = 0)
    {
        $ip = $this->remoteAddress->getRemoteAddress();
        $this->setEmail($email)
            ->setIp($ip)
            ->setUpdatedAt(date('Y-m-d H:i:s'))
            ->setTrust($trust)
            ->save();

        $this->updateCookie();
        $this->updateCart($this->getHash());
    }

    /**
     * Load visitor by cookie
     *
     * @return $this
     */
    public function loadByCookie()
    {
        $hash = $this->cookieManager->getCookie(self::COOKIE_NAME, false);
        if ($hash) {
            $this->load($hash, 'hash');
        }
        return $this;
    }

    /**
     * Get visitor email address
     */
    public function resolveVisitorEmail()
    {
        if ($this->customerSession->isLoggedIn()) {
            return $this->customerSession->getCustomer()->getEmail();
        }

        if ($email = $this->getEmailByCookie()) {
            return $email;
        }

        if ($email = $this->getEmailByIp()) {
            return $email;
        }
    }

    /**
     * Get email by cookie hash
     *
     * @return bool
     */
    public function getEmailByCookie()
    {
        $hash = $this->cookieManager->getCookie(self::COOKIE_NAME, false);
        if (!$hash) {
            return false;
        }

        $emailCollection = $this->collectionFactory->create()
            ->addFieldToFilter('hash', $hash);

        if ($emailCollection->getSize() == 0) {
            return false;
        }

        foreach ($emailCollection as $email) {
            return $email->getEmail();
        }
    }

    /**
     * Search for email by ip
     *
     * @param $ip
     * @return bool|string
     */
    public function getEmailByIp($ip = false)
    {
        if (!$ip) {
            $ip = $this->remoteAddress->getRemoteAddress();
            if (empty($ip)) {
                return false;
            }
        }

        $emailCollection = $this->collectionFactory->create()
            ->addFieldToFilter('ip', $ip);

        if ($emailCollection->getSize()  > 0) {
            foreach ($emailCollection as $email) {
                return $email->getEmail();
            }
        }

        return $this->searchByIpInOrders($ip);
    }

    /**
     * Search email by ip in quotes and orders
     *
     * @param $ip
     * @return bool|string
     */
    public function searchByIpInOrders($ip)
    {
        $sortOrder = $this->sortOrderBuilder
            ->setField('created_at')
            ->setDirection(\Magento\Framework\Api\SortOrder::SORT_DESC)
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('remote_ip', $ip)
            ->addFilter('customer_email', true, 'notnull')
            ->setSortOrders([$sortOrder])
            ->setPageSize(1)
            ->setCurrentPage(1)
            ->create();

        $quoteCollection = $this->cartRepository->getList($searchCriteria)->getItems();

        if (!empty($quoteCollection)) {
            $quote = reset($quoteCollection);
            return $quote->getCustomerEmail();
        }

        $orderCollection = $this->orderRepository->getList($searchCriteria)->getItems();
        if (!empty($orderCollection)) {
            $order = reset($orderCollection);
            return $order->getCustomerEmail();
        }
        return false;
    }

    /**
     * Update visitor cookie
     *
     * @param $hash
     * @return void
     */
    public function updateCookie($hash = false)
    {
        if (!$hash) {
            $hash = $this->cookieManager->getCookie(self::COOKIE_NAME, false);
        }

        if (!$hash) {
            return false;
        }

        $cookieMetadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setHttpOnly(false)
            ->setDurationOneYear()
            ->setPath('/');

        $this->cookieManager->setPublicCookie(self::COOKIE_NAME, $hash, $cookieMetadata);
    }

    /**
     * Returns visitor hash
     */
    public function getVisitorHash()
    {
        return $hash = $this->cookieManager->getCookie(self::COOKIE_NAME, false);
    }

    public function updateCart($hash)
    {
        $currentCartId = $this->checkoutSession->getQuoteId();
        if (is_numeric($currentCartId)) {
            $this->checkoutSession->getQuote()
                ->setData('visitor_hash', $hash)
                ->save();
        }
    }
}
