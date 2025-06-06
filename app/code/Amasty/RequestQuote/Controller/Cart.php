<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Controller;

use Amasty\RequestQuote\Api\AccountManagementInterface as QuoteAccountManagement;
use Amasty\RequestQuote\Model\ConfigProvider;
use Amasty\RequestQuote\Model\Customer\Manager;
use Amasty\RequestQuote\Model\Email\AdminNotification;
use Amasty\RequestQuote\Model\HidePrice\Provider as HidePriceProvider;
use Amasty\RequestQuote\Model\Registry;
use Magento\Catalog\Controller\Product\View\ViewInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filter\LocalizedToNormalized;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

abstract class Cart extends \Magento\Framework\App\Action\Action implements ViewInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Amasty\RequestQuote\Model\Quote\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Amasty\RequestQuote\Helper\Cart
     */
    protected $cartHelper;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Amasty\RequestQuote\Model\Email\Sender
     */
    protected $emailSender;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $customerSessionFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Amasty\RequestQuote\Helper\Data
     */
    private $configHelper;

    /**
     * @var Manager
     */
    private $accountManagement;

    /**
     * @var CustomerUrl
     */
    private $customerUrl;

    /**
     * @var AuthenticationInterface
     */
    private $authentication;

    /**
     * @var PhpCookieManager
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var AdminNotification
     */
    private $adminNotification;

    /**
     * @var HidePriceProvider
     */
    private $hidePriceProvider;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var CustomerExtractor
     */
    private $customerExtractor;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Amasty\RequestQuote\Model\UrlResolver
     */
    protected $urlResolver;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var LocalizedToNormalized
     */
    private $localizedToNormalized;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var QuoteAccountManagement|null
     */
    private $quoteAccountManagement;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Amasty\RequestQuote\Model\Quote\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Amasty\RequestQuote\Model\Cart $cart,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\EncoderInterface $encoder,
        \Amasty\RequestQuote\Helper\Cart $cartHelper,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Amasty\RequestQuote\Model\Email\Sender $emailSender,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        PriceCurrencyInterface $priceCurrency,
        \Amasty\RequestQuote\Helper\Data $configHelper,
        AdminNotification $adminNotification,
        AccountManagementInterface $accountManagement,
        CustomerUrl $customerUrl,
        AuthenticationInterface $authentication,
        CookieMetadataFactory $cookieMetadataFactory,
        PhpCookieManager $cookieManager,
        HidePriceProvider $hidePriceProvider,
        TimezoneInterface $timezone,
        CustomerExtractor $customerExtractor,
        \Psr\Log\LoggerInterface $logger,
        Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Amasty\RequestQuote\Model\UrlResolver $urlResolver,
        ?LocalizedToNormalized $localizedToNormalized = null,
        ?ConfigProvider $configProvider = null,
        ?QuoteAccountManagement $quoteAccountManagement = null
    ) {
        $this->formKeyValidator = $formKeyValidator;
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        $this->cart = $cart;
        $this->localeResolver = $localeResolver;
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonEncoder = $encoder;
        $this->cartHelper = $cartHelper;
        parent::__construct($context);
        $this->dataObjectFactory = $dataObjectFactory;
        $this->emailSender = $emailSender;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->priceCurrency = $priceCurrency;
        $this->configHelper = $configHelper;
        $this->accountManagement = $accountManagement;
        $this->customerUrl = $customerUrl;
        $this->authentication = $authentication;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieManager = $cookieManager;
        $this->logger = $logger;
        $this->adminNotification = $adminNotification;
        $this->hidePriceProvider = $hidePriceProvider;
        $this->timezone = $timezone;
        $this->customerExtractor = $customerExtractor;
        $this->registry = $registry;
        $this->dateTime = $dateTime;
        $this->urlResolver = $urlResolver;
        $this->redirect = $context->getRedirect();
        $this->localizedToNormalized = $localizedToNormalized
            ?? ObjectManager::getInstance()->get(LocalizedToNormalized::class);
        $this->configProvider = $configProvider ?? ObjectManager::getInstance()->get(ConfigProvider::class);
        $this->quoteAccountManagement = $quoteAccountManagement
            ?? ObjectManager::getInstance()->get(QuoteAccountManagement::class);
    }

    /**
     * Perform customer authentication and RaQ feature state checks
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        $customerSession = $this->getCustomerSession();
        if (!$this->configHelper->isGuestCanQuote()
            && !$customerSession->getCustomerId()
            && !$customerSession->authenticate()
        ) {
            $this->getActionFlag()->set('', 'no-dispatch', true);
            if (!$customerSession->getBeforeRaQUrl()) {
                $customerSession->setBeforeRaQUrl($this->redirect->getRefererUrl());
            }
            $customerSession->setBeforeRaQRequest($request->getParams());
            $customerSession->setBeforeRequestParams($customerSession->getBeforeRaQRequest());
            $customerSession->setBeforeModuleName('amasty_quote');
            $customerSession->setBeforeControllerName('cart');
            $customerSession->setBeforeAction($request->getActionName());

            if ($request->getActionName() == 'add') {
                $this->messageManager->addErrorMessage(
                    __('You must login or register to add items to your quote cart.')
                );
            }
        } elseif (!$this->configHelper->isActive() || !$this->configHelper->isAllowedCustomerGroup()) {
            $this->getActionFlag()->set('', 'no-dispatch', true);
            if ($request->getActionName() == 'add') {
                $this->messageManager->addErrorMessage(
                    'The quotation is disabled for your customer group. Please contact us for details.'
                );
            }
            $this->_response->setRedirect($this->redirect->getRefererUrl());
        }

        return parent::dispatch($request);
    }

    /**
     * @return \Amasty\RequestQuote\Model\Quote\Session
     */
    public function getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    /**
     * @return \Amasty\RequestQuote\Helper\Data
     */
    public function getConfigHelper()
    {
        return $this->configHelper;
    }

    /**
     * @return HidePriceProvider
     */
    public function getHidePriceProvider()
    {
        return $this->hidePriceProvider;
    }

    /**
     * @param null|string $backUrl
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function _goBack($backUrl = null)
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($backUrl || $backUrl = $this->getBackUrl($this->redirect->getRefererUrl())) {
            $resultRedirect->setUrl($backUrl);
        }

        return $resultRedirect;
    }

    /**
     * @param string $url
     * @return bool
     */
    protected function _isInternalUrl($url)
    {
        if (strpos($url, 'http') === false) {
            return false;
        }

        /** @var $store \Magento\Store\Model\Store */
        $store = $this->storeManager->getStore();
        $unsecure = strpos($url, $store->getBaseUrl()) === 0;
        $secure = strpos($url, $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK, true)) === 0;
        return $unsecure || $secure;
    }

    /**
     * @param null $defaultUrl
     *
     * @return mixed|null|string
     */
    protected function getBackUrl($defaultUrl = null)
    {
        $returnUrl = $this->getRequest()->getParam('return_url');
        if ($returnUrl && $this->_isInternalUrl($returnUrl)) {
            $this->messageManager->getMessages()->clear();
            return $returnUrl;
        }

        return $defaultUrl;
    }

    protected function getLocateFilter(): LocalizedToNormalized
    {
        $this->localizedToNormalized->setOptions(['locale' => $this->localeResolver->getLocale()]);
        return $this->localizedToNormalized;
    }

    /**
     * @return \Magento\Framework\Escaper
     */
    public function getEscaper()
    {
        return $this->cartHelper->getEscaper();
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    protected function getCustomerSession()
    {
        return $this->customerSessionFactory->create();
    }

    /**
     * @return AccountManagementInterface
     */
    public function getAccountManagement()
    {
        return $this->accountManagement;
    }

    /**
     * @return CustomerUrl
     */
    public function getCustomerUrl()
    {
        return $this->customerUrl;
    }

    /**
     * @return AuthenticationInterface
     */
    public function getAuthentication()
    {
        return $this->authentication;
    }

    /**
     * @return PhpCookieManager
     */
    public function getCookieManager()
    {
        return $this->cookieManager;
    }

    /**
     * @return CookieMetadataFactory
     */
    public function getCookieMetadataFactory()
    {
        return $this->cookieMetadataFactory;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return AdminNotification
     */
    public function getAdminNotification()
    {
        return $this->adminNotification;
    }

    /**
     * @return TimezoneInterface
     */
    public function getTimezone(): TimezoneInterface
    {
        return $this->timezone;
    }

    /**
     * @return CustomerExtractor
     */
    public function getCustomerExtractor(): CustomerExtractor
    {
        return $this->customerExtractor;
    }

    protected function getConfigProvider(): ConfigProvider
    {
        return $this->configProvider;
    }

    protected function getQuoteAccountManagement(): QuoteAccountManagement
    {
        return $this->quoteAccountManagement;
    }
}
