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

namespace Magetrend\AbandonedCart\Helper;

/**
 * Module general helper class
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_IS_AVTICE = 'abandonedcart/general/is_active';

    const XML_PATH_EMAIL_SEND_FROM = 'abandonedcart/email/from';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    public $timezone;

    public $searchCriteriaBuilder;

    public $orderRepository;

    public $quoteRepository;

    public $filterBuilder;

    public $devModeFlag = null;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder
    ) {
        $this->storeManager = $storeManagerInterface;
        $this->timezone = $timezone;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->quoteRepository = $quoteRepository;
        $this->filterBuilder = $filterBuilder;
        $this->orderRepository = $orderRepository;
        parent::__construct($context);
    }

    public function isActive($store = null)
    {
        if ($this->scopeConfig->getValue(
            self::XML_PATH_IS_AVTICE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        )) {
            return true;
        }
        return false;
    }

    public function isDevMode()
    {
        if ($this->devModeFlag === null) {
            if ($this->scopeConfig->getValue(
                self::XML_PATH_IS_DEV_MODE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                0
            )) {
                $this->devModeFlag = true;
            } else {
                $this->devModeFlag = false;
            }
        }

        return $this->devModeFlag;
    }

    public function getSendFrom($storeId = 0, $key = false)
    {
        $from = $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_SEND_FROM,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $from = $this->scopeConfig->getValue(
            'trans_email/'.$from,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (!empty($key) && isset($from[$key])) {
            return $from[$key];
        }
        return $from;
    }

    public function getSendFromEmail($storeId = 0)
    {
        $from = $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_SEND_FROM,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $from = $this->scopeConfig->getValue(
            'trans_email/'.$from,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $from['email'];
    }

    public function getDefaultEmailVariables($storeId)
    {
        return [];
    }

    /**
     * @param Y-m-d H:i:s string $date
     * @param $storeId
     * @param int $type
     * @return string
     */
    public function formatDate($date, $storeId, $type = \IntlDateFormatter::MEDIUM)
    {
        $localeCode = $this->scopeConfig
            ->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        return $this->timezone->formatDateTime($date, $type, \IntlDateFormatter::NONE, $localeCode);
    }

    /**
     * @param Y-m-d H:i:s string $date
     * @param $storeId
     * @param int $type
     * @return string
     */
    public function formatDateTime($date, $storeId, $type = \IntlDateFormatter::MEDIUM)
    {
        $localeCode = $this->scopeConfig
            ->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        return $this->timezone->formatDateTime($date, $type, \IntlDateFormatter::SHORT, $localeCode);
    }

    public function lookupForCart($dateFrom, $email, $visitorHash = '')
    {
        $this->searchCriteriaBuilder
            ->addFilter('created_at', $dateFrom, 'gt');

        $emailFilter = $this->filterBuilder
            ->setField('customer_email')
            ->setValue($email)
            ->create();

        if (!empty($visitorHash)) {
            $hashFilter = $this->filterBuilder
                ->setField('visitor_hash')
                ->setValue($visitorHash)
                ->create();
            $this->searchCriteriaBuilder->addFilters([$emailFilter, $hashFilter]);
        } else {
            $this->searchCriteriaBuilder->addFilters([$emailFilter]);
        }

        $search = $this->searchCriteriaBuilder->setPageSize(1)
            ->setCurrentPage(1)
            ->create();

        $cartList = $this->quoteRepository->getList($search);

        if ($cartList->getTotalCount() == 0) {
            return false;
        }

        foreach ($cartList->getItems() as $cart) {
            return $cart;
        }

        return false;
    }

    public function lookupForNewOrder($from, $email)
    {

        $dateFrom = strtotime($from) - 86400;
        $dateFrom = date('Y-m-d H:i:s', $dateFrom);
        $search = $this->searchCriteriaBuilder
            ->addFilter('created_at', $dateFrom, 'from')
            ->addFilter('customer_email', $email, 'eq')
            ->setPageSize(1)
            ->setCurrentPage(1)
            ->create();

        $orders = $this->orderRepository->getList($search);

        if ($orders->getTotalCount() == 0) {
            return false;
        }

        foreach ($orders->getItems() as $order) {
            if ((strtotime($order->getCreatedAt())+10) >= strtotime($from)) {
                return $order;
            }
        }

        return false;
    }

    public function log($message)
    {
        if ($this->isDevMode()) {
            $this->_logger->debug($message);
        }
    }
}
