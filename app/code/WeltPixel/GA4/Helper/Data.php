<?php

namespace WeltPixel\GA4\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CACHE_ID_CATEGORIES = 'weltpixel_ga4_cached_categories';
    /**
     * @var array
     */
    protected $_gtmOptions;

    /**
     * @var array
     */
    protected $_brandOptions;

    /**
     * @var \Magento\Framework\View\Element\BlockFactory
     */
    protected $blockFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var array
     */
    protected $storeCategories;

    /**
     * @var int
     */
    protected $rootCategoryId;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category
     */
    protected $resourceCategory;

    /**
     * @var \Magento\Framework\Escaper $escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Checkout\Model\Session\SuccessValidator
     */
    protected $checkoutSuccessValidator;

    /**
     * @var \WeltPixel\GA4\Model\Storage
     */
    protected $storage;

    /**
     * @var \Magento\Cookie\Helper\Cookie
     */
    protected $cookieHelper;

    /**
     * @var \Magento\Catalog\Helper\Product\Configuration
     */
    protected $configurationHelper;

    /**
     * @var \Magento\Catalog\Api\ProductCustomOptionRepositoryInterface
     */
    protected $productOptionRepository;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;

    /**
     * @var \WeltPixel\GA4\Model\Dimension
     */
    protected $dimensionModel;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var  \Magento\Framework\App\Cache\StateInterface
     */
    protected $cacheState;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /** @var \Magento\Framework\Session\SessionManagerInterface */
    protected $session;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\DataObject\Factory
     */
    protected $objectFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    protected const XML_PATH_DEV_MOVE_JS_TO_BOTTOM = 'dev/js/move_script_to_bottom';

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\View\Element\BlockFactory $blockFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category $resourceCategory
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Checkout\Model\Session\SuccessValidator $checkoutSuccessValidator
     * @param \WeltPixel\GA4\Model\Storage $storage
     * @param \Magento\Cookie\Helper\Cookie $cookieHelper
     * @param \Magento\Catalog\Helper\Product\Configuration $configurationHelper
     * @param \Magento\Catalog\Api\ProductCustomOptionRepositoryInterface $productOptionRepository
     * @param \Magento\Framework\View\Page\Config $pageConfig
     * @param \WeltPixel\GA4\Model\Dimension $dimensionModel
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\DataObject\Factory $objectFactory
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Category $resourceCategory,
        \Magento\Framework\Escaper $escaper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Checkout\Model\Session\SuccessValidator $checkoutSuccessValidator,
        \WeltPixel\GA4\Model\Storage $storage,
        \Magento\Cookie\Helper\Cookie $cookieHelper,
        \Magento\Catalog\Helper\Product\Configuration $configurationHelper,
        \Magento\Catalog\Api\ProductCustomOptionRepositoryInterface $productOptionRepository,
        \Magento\Framework\View\Page\Config $pageConfig,
        \WeltPixel\GA4\Model\Dimension $dimensionModel,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\DataObject\Factory $objectFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\App\Response\RedirectInterface $redirect
    ) {
        parent::__construct($context);
        $this->_gtmOptions = $this->scopeConfig->getValue('weltpixel_ga4', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->blockFactory = $blockFactory;
        $this->registry = $registry;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->resourceCategory = $resourceCategory;
        $this->escaper = $escaper;
        $this->storeCategories = [];
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->checkoutSuccessValidator = $checkoutSuccessValidator;
        $this->storage = $storage;
        $this->cookieHelper = $cookieHelper;
        $this->configurationHelper = $configurationHelper;
        $this->productOptionRepository = $productOptionRepository;
        $this->pageConfig = $pageConfig;
        $this->dimensionModel = $dimensionModel;
        $this->cache = $cache;
        $this->cacheState = $cacheState;
        $this->priceCurrency = $priceCurrency;
        $this->session = $session;
        $this->cookieManager = $cookieManager;
        $this->objectFactory = $objectFactory;
        $this->resourceConnection = $resourceConnection;
        $this->redirect = $redirect;
    }

    /**
     * Get all categories id, name for the current store view
     */
    private function _populateStoreCategories()
    {
        if (!$this->isEnabled() || !empty($this->storeCategories)) {
            return;
        }

        $this->rootCategoryId = $this->storeManager->getStore()->getRootCategoryId();
        $storeId = $this->storeManager->getStore()->getStoreId();

        $isWpGA4CacheEnabled = $this->cacheState->isEnabled(\WeltPixel\GA4\Model\Cache\Type::TYPE_IDENTIFIER);
        $cacheKey = self::CACHE_ID_CATEGORIES . '-' . $this->rootCategoryId . '-' . $storeId;
        if ($isWpGA4CacheEnabled) {
            $this->_eventManager->dispatch('weltpixel_ga4_cachekey_after', ['cache_key' => $cacheKey]);

            $cachedCategoriesData = $this->cache->load($cacheKey);
            if ($cachedCategoriesData) {
                $this->storeCategories = json_decode($cachedCategoriesData, true);
                return;
            }
        }

        $categories = $this->categoryCollectionFactory->create()
            ->setStoreId($storeId)
            ->addAttributeToFilter('path', ['like' => "1/{$this->rootCategoryId}%"])
            ->addAttributeToSelect('name');

        foreach ($categories as $categ) {
            $this->storeCategories[$categ->getData('entity_id')] = [
                'name' => $categ->getData('name'),
                'path' => $categ->getData('path')
            ];
        }

        if ($isWpGA4CacheEnabled) {
            $cachedCategories = json_encode($this->storeCategories);
            $this->cache->save($cachedCategories, $cacheKey, [\WeltPixel\GA4\Model\Cache\Type::CACHE_TAG]);
        }
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->_gtmOptions['general']['enable'];
    }

    /**
     * @return int
     */
    public function getCurrentWebsiteId()
    {
        return $this->storeManager->getWebsite()->getId();
    }

    /**
     * @return boolean
     */
    public function isProductClickTrackingEnabled()
    {
        return $this->isDataLayerProductClickEnabled();
    }

    /**
     * @return boolean
     */
    public function isDataLayerProductClickEnabled()
    {
        if (!isset($this->_gtmOptions['general']['product_click_tracking'])) {
            return false;
        }
        return $this->_gtmOptions['general']['product_click_tracking'];
    }

    /**
     * @return bool
     */
    public function isCookieRestrictionModeEnabled()
    {
        return $this->scopeConfig->getValue(\Magento\Cookie\Helper\Cookie::XML_PATH_COOKIE_RESTRICTION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getBrandAttribute()
    {
        return $this->_gtmOptions['general']['brand_attribute'];
    }

    /**
     * @return boolean
     */
    public function trackPromotions()
    {
        return $this->_gtmOptions['general']['promotion_tracking'];
    }

    /**
    * @return int
    */
    public function getPersistentStorageExpiryTime()
    {
        return $this->_gtmOptions['general']['persistentlayer_expiry'];
    }

    /**
     * @return boolean
     */
    public function excludeTaxFromTransaction()
    {
        return $this->_gtmOptions['general']['exclude_tax_from_transaction'];
    }

    /**
     * @return boolean
     */
    public function excludeShippingFromTransaction()
    {
        return $this->_gtmOptions['general']['exclude_shipping_from_transaction'];
    }

    /**
     * @return boolean
     */
    public function excludeShippingFromTransactionIncludingTax()
    {
        return $this->_gtmOptions['general']['exclude_shipping_from_transaction_including_tax'];
    }

    /**
     * @return boolean
     */
    public function excludeFreeOrderFromPurchaseForGoogleAnalytics()
    {
        return $this->_gtmOptions['general']['exclude_free_purchase'];
    }

    /**
     * @return boolean
     */
    public function excludeOrderByStatusFlag($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'weltpixel_ga4/general/exclude_order_by_status_flag',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return string[]
     */
    public function getExcludeOrderByStatuses($storeId = null)
    {
        $excludedOrdersStatuses = $this->scopeConfig->getValue(
            'weltpixel_ga4/general/exclude_order_by_statuses',
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ?? '';
        return explode(',', $excludedOrdersStatuses);
    }

    /**
     * @param $order
     * @return bool
     */
    public function isOrderTrackingAllowedBasedOnOrderStatus($order)
    {
        $orderStatuses = $this->getExcludeOrderByStatuses($order->getStoreId());
        $excludeOrderByStatusFlag = $this->excludeOrderByStatusFlag($order->getStoreId());

        if (!$excludeOrderByStatusFlag) {
            return true;
        }

        if (!empty($orderStatuses) && in_array($order->getStatus(), $orderStatuses)) {
            return false;
        }

        return true;
    }

    /**
     * @return boolean
     */
    public function excludeFreeOrderFromAdwordsConversionTracking()
    {
        return $this->_gtmOptions['adwords_conversion_tracking']['exclude_free_purchase'];
    }

    /**
     * @return boolean
     */
    public function excludeFreeOrderFromAdwordsRemarketing()
    {
        return $this->_gtmOptions['adwords_remarketing']['exclude_free_purchase'];
    }

    /**
     * return child or parent string
     * @return string
     */
    public function getParentOrChildIdUsage()
    {
        return $this->_gtmOptions['general']['parent_vs_child'];
    }

    /**
     * @return bool
     */
    public function getSecureCookiesFlag()
    {
        return $this->_gtmOptions['general']['secure_cookies'];
    }

    /**
     * @return int
     */
    public function getImpressionChunkSize()
    {
        return $this->_gtmOptions['general']['impression_chunk_size'];
    }

    /**
     * @return string
     */
    public function getGtmCodeSnippet()
    {
        return trim($this->_gtmOptions['general']['gtm_code'] ?? '');
    }

    /**
     * @return string
     */
    public function getGtmCodeSnippetForHead()
    {
        $gtmCodeSnippet = $this->getGtmCodeSnippet();
        if ($this->isDevMoveJsBottomEnabled()) {
            $gtmCodeSnippet = str_replace('<script>', '<script exclude-this-tag="text/x-magento-template">', $gtmCodeSnippet);
        }
        return $gtmCodeSnippet;
    }

    /**
     * @return string
     */
    public function getGtmNonJsCodeSnippet()
    {
        return trim($this->_gtmOptions['general']['gtm_nonjs_code'] ?? '');
    }

    /**
     * @return string
     */
    public function getCustomSuccessPagePaths()
    {
        return trim($this->_gtmOptions['general']['success_page_paths'] ?? '');
    }

    /**
     * @return string
     */
    public function getCustomCheckoutPagePaths()
    {
        return trim($this->_gtmOptions['general']['checkout_page_paths'] ?? '');
    }

    /**
     * @return string
     */
    public function getDataLayerScript()
    {
        $script = '';

        if (!($block = $this->createBlock('Core', 'datalayer.phtml'))) {
            return $script;
        }

        $block->setNameInLayout('wp.ga4.datalayer.scripts');

        $this->addDefaultInformation();
        $this->addCategoryPageInformation();
        $this->addSearchResultPageInformation();
        $this->addProductPageInformation();
        $this->addCartPageInformation();
        $this->addCheckoutInformation();
        $this->addOrderInformation();

        $html = $block->toHtml();

        return $html;
    }

    /**
     * @param $blockName
     * @param $template
     * @return bool
     */
    protected function createBlock($blockName, $template)
    {
        if ($block = $this->blockFactory->createBlock('\WeltPixel\GA4\Block\\' . $blockName)
            ->setTemplate('WeltPixel_GA4::' . $template)
        ) {
            return $block;
        }

        return false;
    }

    /**
     * @param $blockName
     * @param $template
     * @return bool
     */
    public function createGA4Block($blockName, $templae)
    {
        return $this->createBlock($blockName, $templae);
    }

    /**
     * Set default gtm options based on configuration
     */
    public function addDefaultInformation()
    {
        $actionName = $this->_request->getFullActionName();

        $customCheckoutPagePaths = trim($this->getCustomCheckoutPagePaths());
        $requestPath = str_replace("_", "/", $actionName);
        $requestPathDynamic  = false;

        if (strlen($customCheckoutPagePaths)) {
            $customCheckoutPagePaths = explode(",", $customCheckoutPagePaths);
            $customCheckoutPagePaths = array_map('trim', $customCheckoutPagePaths);
            $requestPathDynamic  = str_replace("_", "/", $actionName);
            $lastSlashPosition = strrpos($requestPathDynamic, "/");
            $requestPathDynamic = substr_replace($requestPathDynamic, '*', $lastSlashPosition + 1);
        }

        if ($this->isCustomDimensionPageNameEnabled()) {
            if ($this->pageConfig) {
                $pageTitle = $this->pageConfig->getTitle()->get();
                $this->storage->setData('pageName', $pageTitle);
            }
        }

        if ($this->isCustomDimensionPageTypeEnabled()) {
            $pageType = \WeltPixel\GA4\Model\Api\Remarketing::ECOMM_PAGETYPE_OTHER;
            switch ($actionName) {
                case 'cms_index_index':
                    $pageType = \WeltPixel\GA4\Model\Api\Remarketing::ECOMM_PAGETYPE_HOME;
                    break;
                case 'checkout_index_index':
                case 'firecheckout_index_index':
                    $pageType = \WeltPixel\GA4\Model\Api\Remarketing::ECOMM_PAGETYPE_CHECKOUT;
                    break;
            }

            if ($requestPathDynamic) {
                if (in_array($requestPath, $customCheckoutPagePaths) || in_array($requestPathDynamic, $customCheckoutPagePaths)) {
                    $pageType = \WeltPixel\GA4\Model\Api\Remarketing::ECOMM_PAGETYPE_CHECKOUT;
                }
            }


            $this->storage->setData('pageType', $pageType);
        }

        if ($this->isAdWordsRemarketingEnabled()) {
            $remarketingData = [];
            switch ($actionName) {
                case 'cms_index_index':
                    $remarketingData['ecomm_pagetype'] = \WeltPixel\GA4\Model\Api\Remarketing::ECOMM_PAGETYPE_HOME;
                    break;
                case 'checkout_index_index':
                case 'firecheckout_index_index':
                    $remarketingData['ecomm_pagetype'] = \WeltPixel\GA4\Model\Api\Remarketing::ECOMM_PAGETYPE_CHECKOUT;
                    break;
                default:
                    $remarketingData['ecomm_pagetype'] = \WeltPixel\GA4\Model\Api\Remarketing::ECOMM_PAGETYPE_OTHER;
                    break;
            }

            if ($requestPathDynamic) {
                if (in_array($requestPath, $customCheckoutPagePaths) || in_array($requestPathDynamic, $customCheckoutPagePaths)) {
                    $remarketingData['ecomm_pagetype'] = \WeltPixel\GA4\Model\Api\Remarketing::ECOMM_PAGETYPE_CHECKOUT;
                }
            }

            $this->storage->setData('google_tag_params', $remarketingData);
        }
    }

    /**
     * Set category page product impressions
     */
    public function addCategoryPageInformation()
    {
        $currentCategory = $this->getCurrentCategory();

        if (!empty($currentCategory)) {
            $categoryBlock = $this->createBlock('Category', 'category.phtml');

            if ($categoryBlock) {
                $categoryBlock->setCurrentCategory($currentCategory);
                $categoryBlock->toHtml();
            }

            if ($this->isCustomDimensionPageTypeEnabled()) {
                $pageType = \WeltPixel\GA4\Model\Api\Remarketing::ECOMM_PAGETYPE_CATEGORY;
                $this->storage->setData('pageType', $pageType);
            }
        }
    }

    /**
     * @return mixed
     */
    public function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }

    /**
     * Set search result page product impressions
     */
    public function addSearchResultPageInformation()
    {
        $moduleName = $this->_request->getModuleName();
        $controllerName = $this->_request->getControllerName();
        $listPrefix = '';
        if ($controllerName == 'advanced') {
            $listPrefix = __('Advanced');
        }

        if ($moduleName == 'catalogsearch') {
            if ($this->isCustomDimensionPageTypeEnabled()) {
                $pageType = \WeltPixel\GA4\Model\Api\Remarketing::ECOMM_PAGETYPE_SEARCHRESULTS;
                $this->storage->setData('pageType', $pageType);
            }

            $searchBlock = $this->createBlock('Search', 'search.phtml');

            if ($searchBlock) {
                $searchBlock->setListPrefix($listPrefix);
                return $searchBlock->toHtml();
            }
        }
    }

    /**
     * Set product page detail infromation
     */
    public function addProductPageInformation()
    {
        $currentProduct = $this->getCurrentProduct();

        if (!empty($currentProduct)) {
            $productBlock = $this->createBlock('Product', 'product.phtml');

            if ($productBlock) {
                $productBlock->setCurrentProduct($currentProduct);
                $productBlock->toHtml();
            }

            if ($this->isCustomDimensionPageTypeEnabled()) {
                $pageType = \WeltPixel\GA4\Model\Api\Remarketing::ECOMM_PAGETYPE_PRODUCT;
                $this->storage->setData('pageType', $pageType);
            }
        }
    }

    /**
     * @return mixed
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * Set crossell productImpressions
     */
    public function addCartPageInformation()
    {
        $requestPath = $this->_request->getModuleName() .
            DIRECTORY_SEPARATOR . $this->_request->getControllerName() .
            DIRECTORY_SEPARATOR . $this->_request->getActionName();

        if ($requestPath == 'checkout/cart/index') {
            $cartBlock = $this->createBlock('Cart', 'cart.phtml');

            if ($cartBlock) {
                $quote = $this->checkoutSession->getQuote();
                $cartBlock->setQuote($quote);
                $cartBlock->toHtml();
            }

            if ($this->isCustomDimensionPageTypeEnabled()) {
                $pageType = \WeltPixel\GA4\Model\Api\Remarketing::ECOMM_PAGETYPE_CART;
                $this->storage->setData('pageType', $pageType);
            }
        }
    }

    public function addCheckoutInformation()
    {
        $requestPath = $this->_request->getModuleName() .
            DIRECTORY_SEPARATOR . $this->_request->getControllerName() .
            DIRECTORY_SEPARATOR . $this->_request->getActionName();

        $requestPathDynamic = $this->_request->getModuleName() .
            DIRECTORY_SEPARATOR . $this->_request->getControllerName() .
            DIRECTORY_SEPARATOR . '*';

        $checkoutPagePaths = [
            'checkout/index/index',
            'firecheckout/index/index'
        ];

        $customCheckoutPagePaths = trim($this->getCustomCheckoutPagePaths());

        if (strlen($customCheckoutPagePaths)) {
            $checkoutPagePaths = array_merge($checkoutPagePaths, array_map('trim', explode(",", $customCheckoutPagePaths)));
        }

        if (!in_array($requestPath, $checkoutPagePaths) && !in_array($requestPathDynamic, $checkoutPagePaths)) {
            return;
        }


        $checkoutBlock = $this->createBlock('Checkout', 'checkout.phtml');

        if ($checkoutBlock) {
            $quote = $this->checkoutSession->getQuote();
            $checkoutBlock->setQuote($quote);
            $checkoutBlock->toHtml();
        }

        if ($this->isCustomDimensionPageTypeEnabled()) {
            $pageType = \WeltPixel\GA4\Model\Api\Remarketing::ECOMM_PAGETYPE_CHECKOUT;
            $this->storage->setData('pageType', $pageType);
        }
    }

    /**
     * Set purchase details
     */
    public function addOrderInformation()
    {
        $lastOrderId = $this->checkoutSession->getLastOrderId();
        $requestPath = $this->_request->getModuleName() .
            DIRECTORY_SEPARATOR . $this->_request->getControllerName() .
            DIRECTORY_SEPARATOR . $this->_request->getActionName();

        $requestPathDynamic = $this->_request->getModuleName() .
            DIRECTORY_SEPARATOR . $this->_request->getControllerName() .
            DIRECTORY_SEPARATOR . '*';

        $successPagePaths = [
            'checkout/onepage/success'
        ];

        $customSuccessPagePaths = trim($this->getCustomSuccessPagePaths());

        if (strlen($customSuccessPagePaths)) {
            $successPagePaths = array_merge($successPagePaths, array_map('trim', explode(",", $customSuccessPagePaths)));
        }

        if (!$lastOrderId) {
            return;
        }

        if (!in_array($requestPath, $successPagePaths) && !in_array($requestPathDynamic, $successPagePaths)) {
            return;
        }

        $orderBlock = $this->createBlock('Order', 'order.phtml');
        if ($orderBlock) {
            $order = $this->orderRepository->get($lastOrderId);
            $orderBlock->setOrder($order);
            $orderBlock->toHtml();
        }

        if ($this->isCustomDimensionPageTypeEnabled()) {
            $pageType = \WeltPixel\GA4\Model\Api\Remarketing::ECOMM_PAGETYPE_PURCHASE;
            $this->storage->setData('pageType', $pageType);
        }

        $serverSideOrderTracking = $this->createBlock('Track\\Order', 'serverside/checkout/success.phtml');
        if ($serverSideOrderTracking) {
            $order = $this->orderRepository->get($lastOrderId);
            $serverSideOrderTracking->setOrder($order);
            $serverSideOrderTracking->toHtml();
        }
    }

    /**
     * @return string
     */
    public function getAffiliationName()
    {
        return $this->storeManager->getWebsite()->getName() . ' - ' .
            $this->storeManager->getGroup()->getName() . ' - ' .
            $this->storeManager->getStore()->getName();
    }

    /**
     * @param $productOptions
     * @param $productType
     * @return bool|string
     */
    public function checkVariantForProductOptions($productOptions, $productType)
    {
        $variant = [];
        if ($productType == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            if (isset($productOptions['attributes_info'])) {
                foreach ($productOptions['attributes_info'] as $attribute) {
                    $variant[] = $attribute['label'] . ": " . $attribute['value'];
                }
            }
        }

        // Variant for Custom Options
        if (isset($productOptions['options'])) {
            foreach ($productOptions['options'] as $option) {
                $variant[] = $option['label'] . ": " . $option['print_value'];
            }
        }

        if ($variant) {
            return implode(' | ', $variant);
        }

        return false;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param array $buyRequest
     * @param \Magento\Wishlist\Model\Item $wishlistItem
     * @param boolean $checkForCustomOptions
     * @return bool|string
     */
    public function checkVariantForProduct($product, $buyRequest = [], $wishlistItem = null, $checkForCustomOptions = false)
    {
        $variant = [];

        /** get the configurable products variants, options */
        if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $options = $product->getTypeInstance(true)->getSelectedAttributesInfo($product);
            foreach ($options as $option) {
                $variant[] = $option['label'] . ": " . $option['value'];
            }

            if (!$variant && isset($buyRequest['super_attribute'])) {
                $superAttributeLabels = [];
                $superAttributeOptions = [];
                $_attributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);
                foreach ($_attributes as $_attribute) {
                    $superAttributeLabels[$_attribute['attribute_id']] = $_attribute['label'];
                    foreach ($_attribute->getOptions() as $option) {
                        $superAttributeOptions[$_attribute['attribute_id']][$option['value_index']] = $option['store_label'];
                    }
                }

                foreach ($buyRequest['super_attribute'] as $id => $value) {
                    $variant[] = $superAttributeLabels[$id] . ": " . $superAttributeOptions[$id][$value];
                }
            }
        }

        $customOptionFound = false;
        /** This is for the custom options for products */
        $_customOptions = $product->getTypeInstance(true)->getOrderOptions($product);
        if (array_key_exists('options', $_customOptions)) {
            foreach ($_customOptions['options'] as $option) {
                $customOptionFound = true;
                $variant[] = $option['label'] . ": " . $option['print_value'];
            }
        }

        if ($wishlistItem && !$customOptionFound) {
            $options = $this->configurationHelper->getOptions($wishlistItem);
            foreach ($options as $customOption) {
                if (isset($customOption['print_value'])) {
                    $variant[] = $customOption['label'] . ": " . $customOption['print_value'];
                }
            }
        }

        /** Wishlist add to cart with not preselected custom options */
        if ($checkForCustomOptions && isset($buyRequest['options'])) {
            $productOptions = $this->productOptionRepository->getProductOptions($product);
            $productCustomOptionsLabel = [];
            $productCustomOptionsValues = [];
            foreach ($productOptions as $option) {
                $productCustomOptionsLabel[$option['option_id']] = $option['title'];
                if ($option->hasValues()) {
                    $values = $option->getValues();
                    foreach ($values as $value) {
                        $productCustomOptionsValues[$option['option_id']][$value->getOptionTypeId()] = $value->getTitle();
                    }
                }
            }

            foreach ($buyRequest['options'] as $optionId => $optionValues) {
                if (is_array($optionValues)) {
                    $optValue = [];
                    foreach ($optionValues as $value) {
                        $optValue[] = $productCustomOptionsValues[$optionId][$value];
                    }
                    $variant[] = $productCustomOptionsLabel[$optionId] . ": " . implode(',', $optValue);
                } elseif (isset($productCustomOptionsValues[$optionId])) {
                    $variant[] = $productCustomOptionsLabel[$optionId] . ": " . $productCustomOptionsValues[$optionId][$optionValues];
                } else {
                    $variant[] = $productCustomOptionsLabel[$optionId] . ": " . $optionValues;
                }
            }
        }

        if ($variant) {
            return implode(' | ', $variant);
        }

        return false;
    }

    /**
     * @param int $qty
     * @param \Magento\Catalog\Model\Product $product
     * @param array $buyRequest
     * @param boolean $checkForCustomOptions
     * @return array
     */
    public function addToCartPushData($qty, $product, $buyRequest = [], $checkForCustomOptions = false)
    {
        $result = [];

        $displayOption = $this->getParentOrChildIdUsage();
        $productId = $this->getGtmProductId($product);
        if ($buyRequest instanceof \Magento\Framework\DataObject) {
            $buyRequest = $buyRequest->getData();
        }
        $itemName = html_entity_decode($product->getName() ?? '');

        if ( ($displayOption == \WeltPixel\GA4\Model\Config\Source\ParentVsChild::CHILD) && ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)) {
            $canditatesRequest = $this->objectFactory->create($buyRequest);
            $cartCandidates = $product->getTypeInstance()->prepareForCartAdvanced($canditatesRequest, $product);

            if (is_array($cartCandidates) || is_object($cartCandidates)) {
                foreach ($cartCandidates as $candidate) {
                    if ($candidate->getParentProductId()) {
                        $productId = $this->getGtmProductId($candidate);
                        $itemName = html_entity_decode($candidate->getName() ?? '');
                    }
                }
            }
        }

        $result['event'] = 'add_to_cart';
        $result['ecommerce'] = [];
        $result['ecommerce']['items'] = [];

        $productData = [];
        $productData['item_name'] = $itemName;
        $productData['affiliation'] = $this->getAffiliationName();
        $productData['item_id'] = $productId;
        if ($this->checkoutSession->getGA4LastProductPrice()) {
            $productData['price'] = floatval(number_format($this->convertPriceToCurrentCurrency($this->checkoutSession->getGA4LastProductPrice()), 2, '.', ''));
            $this->checkoutSession->setGA4LastProductPrice(null);
        } else {
            $productData['price'] = floatval(number_format($product->getPriceInfo()->getPrice('final_price')->getValue(), 2, '.', ''));
        }

        if ($this->isBrandEnabled()) {
            $productData['item_brand'] = $this->getGtmBrand($product);
        }

        $productCategoryIds = $product->getCategoryIds();
        $categoryName = $this->getGtmCategoryFromCategoryIds($productCategoryIds);
        $ga4Categories = $this->getGA4CategoriesFromCategoryIds($productCategoryIds);
        $productData = array_merge($productData, $ga4Categories);
        $productData['item_list_name'] = $categoryName;
        $productData['item_list_id'] = count($productCategoryIds) ? $productCategoryIds[0] : '';
        $productData['quantity'] = (double)$qty;

        /**  Set the custom dimensions */
        $customDimensions = $this->dimensionModel->getProductDimensions($product, $this);
        foreach ($customDimensions as $name => $value) :
            $productData[$name] = $value;
        endforeach;

        if ($this->isVariantEnabled()) {
            $variant = $this->checkVariantForProduct($product, $buyRequest, null, $checkForCustomOptions);
            if ($variant) {
                $productData['item_variant'] = $variant;
            }
        }

        $result['ecommerce']['currency'] =  $this->getCurrencyCode();
        $result['ecommerce']['value'] = $productData['price'] * abs($qty);;
        $result['ecommerce']['items'][] = $productData;

        return $result;
    }

    /**
     * @param array $currentAddToCartData
     * @param array $addToCartPushData
     * @return array
     */
    public function mergeAddToCartPushData($currentAddToCartData, $addToCartPushData)
    {
        if (!is_array($currentAddToCartData)) {
            $currentAddToCartData = $addToCartPushData;
        } else {
            $currentAddToCartData['ecommerce']['items'][] = $addToCartPushData['ecommerce']['items'][0];
        }

        return $currentAddToCartData;
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->storeManager->getStore()->getCurrentCurrencyCode();
    }

    /**
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        return $this->storeManager->getStore()->getBaseCurrencyCode();
    }

    /**
     * @param float $price
     * @param string $currencyCode
     * @return float
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function convertPriceToCurrentCurrency($price)
    {
        if ($this->getCurrencyCode() != $this->getBaseCurrencyCode()) {
            return $this->priceCurrency->convert($price, $this->storeManager->getStore(), $this->getCurrencyCode());
        }

        return $price;

    }

    /**
     * Returns the product id or sku based on the backend settings
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getGtmProductId($product)
    {
        $idOption = $this->getIdSelection();
        $gtmProductId = '';

        switch ($idOption) {
            case 'sku':
                $gtmProductId = $product->getData('sku');
                break;
            case 'id':
            default:
                $gtmProductId = $product->getId();
                break;
        }

        return $gtmProductId;
    }

    /**
     * Get the product id or sku for order item
     * @param \Magento\Sales\Model\Order\Item $item
     * @return string
     */
    public function getGtmOrderItemId($item)
    {
        $idOption = $this->getIdSelection();
        $gtmProductId = '';

        switch ($idOption) {
            case 'sku':
                $gtmProductId = $item->getProduct()->getData('sku');//$item->getSku();
                break;
            case 'id':
            default:
                $gtmProductId = $item->getProductId();
                break;
        }

        return $gtmProductId;
    }

    /**
     * @return string
     */
    public function getIdSelection()
    {
        return $this->_gtmOptions['general']['id_selection'];
    }

    /**
     * @return boolean
     */
    public function isBrandEnabled()
    {
        return $this->_gtmOptions['general']['enable_brand'];
    }

    /**
     * @return mixed
     */
    public function isVariantEnabled()
    {
        return $this->_gtmOptions['general']['enable_variant'];
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getGtmBrand($product)
    {
        $gtmBrand = '';
        if ($this->isBrandEnabled()) {
            $brandAttribute = $this->_gtmOptions['general']['brand_attribute'];
            $brandOptions = $this->getBrandOptions($product, $brandAttribute);

            $frontendValue =  $product->getData($brandAttribute);
            if (is_array($frontendValue)) {
                $result = [];
                foreach ($frontendValue as $value) {
                    $result[] = ($brandOptions[$value]) ? $brandOptions[$value] : null;
                }
                $gtmBrand = implode(',', array_filter($result));
            } elseif (isset($brandOptions[$frontendValue])) {
                $gtmBrand = $brandOptions[$frontendValue];
            }
        }

        return $gtmBrand;
    }

    /**
     * @param  \Magento\Catalog\Model\Product $product
     * @param string $brand
     * @return array
     */
    protected function getBrandOptions($product, $brand)
    {
        if (empty($this->_brandOptions)) {
            try {
                $options = $product->getResource()->getAttribute($brand)->getSource()->getAllOptions();
                foreach ($options as $option) {
                    $this->_brandOptions[$option['value']] = $option['label'];
                }
            } catch (Exception $ex) {
            }
        }

        return $this->_brandOptions;
    }

    /**
     * @return string
     */
    public function getOrderTotalCalculation()
    {
        return $this->_gtmOptions['general']['order_total_calculation'];
    }

    /**
     * Returns category tree path
     * @param array $categoryIds
     * @return string
     */
    public function getGtmCategoryFromCategoryIds($categoryIds)
    {
        if (!count($categoryIds)) {
            return '';
        }

        if (empty($this->storeCategories)) {
            $this->_populateStoreCategories();
        }

        $categoryIds = $this->_filterStoreCategories($categoryIds);
        $categoryId = $categoryIds[0] ?? $this->rootCategoryId;

        $categoryPath = '';
        if (isset($this->storeCategories[$categoryId])) {
            $categoryPath = $this->storeCategories[$categoryId]['path'];
        }

        return str_replace('{#}', '/', $this->_buildCategoryPath($categoryPath));
    }

    /**
     * @param array $categoryIds
     * @return array
     */
    private function _filterStoreCategories($categoryIds)
    {
        $filteredCategoryIds = [];
        foreach ($categoryIds as $categoryId) {
            if (isset($this->storeCategories[$categoryId])) {
                $filteredCategoryIds[] = $categoryId;
            }
        }

        if (count($categoryIds) > 1) {
            $filteredCategoryIds = array_diff_assoc($filteredCategoryIds, [$this->rootCategoryId]);
            $filteredCategoryIds = array_values($filteredCategoryIds);
        }

        return $filteredCategoryIds;
    }

    /**
     * @param string $categoryPath
     * @return string
     */
    private function _buildCategoryPath($categoryPath)
    {
        $categIds = explode('/', $categoryPath);
        $ignoreCategories = 2;
        if (count($categIds) < 3) {
            $ignoreCategories = 1;
        }
        /* first 2 categories can be ignored, or 1st if root category */
        $categoriIds = array_slice($categIds, $ignoreCategories);
        $categoriesWithNames = [];

        foreach ($categoriIds as $categoriId) {
            if (isset($this->storeCategories[$categoriId])) {
                $categoriesWithNames[] = $this->storeCategories[$categoriId]['name'];
            }
        }

        return implode('{#}', $categoriesWithNames);
    }

    /**
     * @param int $qty
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @return array
     */
    public function removeFromCartPushData($qty, $product, $quoteItem)
    {
        $result = [];

        $productId = $this->getGtmProductId($product);
        $itemName = html_entity_decode($product->getName() ?? '');

        $displayOption = $this->getParentOrChildIdUsage();
        if ( ($displayOption == \WeltPixel\GA4\Model\Config\Source\ParentVsChild::CHILD) && ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)) {
            if ($quoteItem->getHasChildren()) {
                foreach ($quoteItem->getChildren() as $child) {
                    $childProduct = $child->getProduct();
                    $productId = $this->getGtmProductId($childProduct);
                    $itemName = html_entity_decode($childProduct->getName() ?? '');
                }
            }
        }

        $result['event'] = 'remove_from_cart';
        $result['ecommerce'] = [];
        $result['ecommerce']['items'] = [];

        $productData = [];
        $productData['item_name'] = $itemName;
        $productData['affiliation'] = $this->getAffiliationName();
        $productData['item_id'] = $productId;
        $productData['price'] = floatval(number_format($this->convertPriceToCurrentCurrency($quoteItem->getPrice()), 2, '.', ''));
        if ($this->isBrandEnabled()) {
            $productData['item_brand'] = $this->getGtmBrand($product);
        }

        $productCategoryIds = $product->getCategoryIds();
        $categoryName = $this->getGtmCategoryFromCategoryIds($productCategoryIds);
        $ga4Categories = $this->getGA4CategoriesFromCategoryIds($productCategoryIds);
        $productData = array_merge($productData, $ga4Categories);
        $productData['item_list_name'] = $categoryName;
        $productData['item_list_id'] = count($productCategoryIds) ? $productCategoryIds[0] : '';
        $productData['quantity'] = (double)$qty;

        if ($this->isVariantEnabled()) {
            $productFromQuote = $quoteItem->getProduct();
            $variant = $this->checkVariantForProduct($productFromQuote);
            if ($variant) {
                $productData['item_variant'] = $variant;
            }
        }

        $result['ecommerce']['currency'] =  $this->getCurrencyCode();
        $result['ecommerce']['value'] = $productData['price'] * abs($qty);
        $result['ecommerce']['items'][] = $productData;

        return $result;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param array $buyRequest
     * @param \Magento\Wishlist\Model\Item $wishlistItem
     * @return array
     */
    public function addToWishListPushData($product, $buyRequest, $wishlistItem)
    {
        $result = [];

        $result['event'] = 'add_to_wishlist';
        $result['ecommerce'] = [];
        $result['ecommerce']['items'] = [];

        $productData = [];
        $productData['item_name'] = html_entity_decode($product->getName() ?? '');
        $productData['affiliation'] = $this->getAffiliationName();
        $productData['item_id'] = $this->getGtmProductId($product);
        $productData['price'] = floatval(number_format($product->getPriceInfo()->getPrice('final_price')->getValue(), 2, '.', ''));
        if ($this->isBrandEnabled()) {
            $productData['item_brand'] = $this->getGtmBrand($product);
        }

        $ga4Categories = $this->getGA4CategoriesFromCategoryIds($product->getCategoryIds());
        $productData = array_merge($productData, $ga4Categories);

        /**  Set the custom dimensions */
        $customDimensions = $this->dimensionModel->getProductDimensions($product, $this);
        foreach ($customDimensions as $name => $value) :
            $productData[$name] = $value;
        endforeach;

        if ($this->isVariantEnabled()) {
            $variant = $this->checkVariantForProduct($product, $buyRequest, $wishlistItem);
            if ($variant) {
                $productData['item_variant'] = $variant;
            }
        }

        $result['ecommerce']['currency'] =  $this->getCurrencyCode();
        $result['ecommerce']['value'] = $productData['price'];
        $result['ecommerce']['items'][] = $productData;

        return $result;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function addToComparePushData($product)
    {
        $result = [];

        $result['event'] = 'add_to_compare';
        $result['ecommerce'] = [];
        $result['ecommerce']['items'] = [];

        $productData = [];
        $productData['item_name'] = html_entity_decode($product->getName() ?? '');
        $productData['affiliation'] = $this->getAffiliationName();
        $productData['item_id'] = $this->getGtmProductId($product);
        $productData['price'] = floatval(number_format($product->getPriceInfo()->getPrice('final_price')->getValue(), 2, '.', ''));
        if ($this->isBrandEnabled()) {
            $productData['item_brand'] = $this->getGtmBrand($product);
        }

        $ga4Categories = $this->getGA4CategoriesFromCategoryIds($product->getCategoryIds());
        $productData = array_merge($productData, $ga4Categories);

        $result['ecommerce']['items'][] = $productData;

        return $result;
    }

    /**
     * @param int $step
     * @param string $checkoutOption
     * @param $order
     * @return array
     */
    public function addCheckoutStepPushData($step, $checkoutOption, $order = null)
    {
        $checkoutStepResult = [];
        $products = [];
        $checkoutBlock = $this->createBlock('Checkout', 'checkout.phtml');
        $couponCode = '';

        if ($checkoutBlock) {
            $quote = $this->checkoutSession->getQuote();
            $checkoutBlock->setQuote($quote);
            if ($order) {
                $orderBlock = $this->createBlock('Order', 'order.phtml');
                $orderBlock->setOrder($order);
                $products = $orderBlock->getProducts();
            } else {
                $products = $checkoutBlock->getProducts();
            }
            $couponCode = $quote->getCouponCode();
        }

        switch ($step) {
            case '1':
                $eventName = 'add_shipping_info';
                $optionName = 'shipping_tier';
                break;
            case '2':
                $eventName = 'add_payment_info';
                $optionName = 'payment_type';
                if (empty($products)) {
                    $lastOrderId = $this->checkoutSession->getLastOrderId();
                    $orderBlock = $this->createBlock('Order', 'order.phtml');
                    if ($orderBlock && $lastOrderId) {
                        $order = $this->orderRepository->get($lastOrderId);
                        $orderBlock->setOrder($order);
                        $products = $orderBlock->getProducts();
                    }
                }
                break;
            default:
                $eventName = '';
                $optionName = '';
        }

        $checkoutStepResult['event'] = $eventName;
        $checkoutStepResult['ecommerce'] = [];
        $checkoutStepResult['ecommerce']['currency'] = $this->getCurrencyCode();
        if ($order) {
            $checkoutStepResult['ecommerce']['value'] = floatval(number_format($order->getGrandTotal(), 2, '.', ''));
        } else {
            $checkoutStepResult['ecommerce']['value'] = floatval(number_format($this->getCartTotal(), 2, '.', ''));
        }

        $checkoutStepResult['ecommerce']['items'] = [];
        $checkoutStepResult['ecommerce']['items'] = $products;
        $checkoutStepResult['ecommerce'][$optionName] = $checkoutOption;

        if ($couponCode) :
            $checkoutStepResult['ecommerce']['coupon'] = $couponCode;
        endif;

        $result = [];
        $result[] = $checkoutStepResult;

        return $result;
    }

    /**
     * @return float
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCartTotal()
    {
        $quote = $this->checkoutSession->getQuote();
        $grandTotal = $quote->getGrandTotal();
        if (!$grandTotal) {
            $lastOrderId = $this->checkoutSession->getLastOrderId();
            if ($lastOrderId) {
                $order = $this->orderRepository->get($lastOrderId);
                $grandTotal = $order->getGrandTotal();
            }
        }

        return $grandTotal ?? 0 ;
    }

    /**
     * @return boolean
     */
    public function isCustomDimensionCustomerIdEnabled()
    {
        return $this->_gtmOptions['general']['custom_dimension_customerid'];
    }

    /**
     * @return boolean
     */
    public function isCustomDimensionCustomerGroupEnabled()
    {
        return $this->_gtmOptions['general']['custom_dimension_customergroup'];
    }

    /**
     * @return boolean
     */
    public function trackStockStatusEnabled()
    {
        return $this->_gtmOptions['general']['track_stockstatus'];
    }

    /**
     * @return boolean
     */
    public function trackReviewsCountEnabled()
    {
        return $this->_gtmOptions['general']['track_reviewscount'];
    }

    /**
     * @return boolean
     */
    public function trackReviewsScoreEnabled()
    {
        return $this->_gtmOptions['general']['track_reviewsscore'];
    }

    /**
     * @return boolean
     */
    public function trackSaleProductEnabled()
    {
        return $this->_gtmOptions['general']['track_saleproduct'];
    }

    /**
     * @return boolean
     */
    public function isCustomDimensionPageNameEnabled()
    {
        return $this->_gtmOptions['general']['custom_dimension_pagename'];
    }

    /**
     * @return boolean
     */
    public function isCustomDimensionPageTypeEnabled()
    {
        return $this->_gtmOptions['general']['custom_dimension_pagetype'];
    }

    /**
     * @return boolean
     */
    public function isAdWordConversionTrackingEnabled()
    {
        return $this->_gtmOptions['adwords_conversion_tracking']['enable'];
    }

    /**
     * @return boolean
     */
    public function isEnhancedConversionsEnabled()
    {
        return $this->_gtmOptions['adwords_conversion_tracking']['enable_enhanced_conversion'];
    }

    /**
     * @return boolean
     */
    public function isAdWordNewCustomerAcquisitionEnabled()
    {
        return $this->_gtmOptions['adwords_conversion_tracking']['enable_new_customer_acquisition'];
    }

    /**
     * @return int
     */
    public function getCustomerPurchaseDayLapse()
    {
        return (int)$this->_gtmOptions['adwords_conversion_tracking']['purchase_day_lapse'] ?? 540;
    }

    /**
     * @return boolean
     */
    public function isConversionWithCartDataEnabled()
    {
        return $this->_gtmOptions['adwords_conversion_tracking']['enable_cart_data'];
    }

    /**
     * @return boolean
     */
    public function isAdWordsRemarketingEnabled()
    {
        return $this->_gtmOptions['adwords_remarketing']['enable'];
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param string $index
     * @param string $list
     * @param string $listId
     * @return string
     */
    public function addProductClick($product, $index = 0, $list = '', $listId = '')
    {
        $html = $this->getProductClickHtml($product, $index, $list, $listId);

        if (!empty($html)) {
            $htmlObject = new \Magento\Framework\DataObject(['html' => $html]);
            $this->_eventManager->dispatch('weltpixel_ga4_afterproductclick', [
                'html' => $htmlObject,
                'product' => $product,
                'index' => $index,
                'list' => $list
            ]);
            $html = $htmlObject->getHtml();
            $html = 'onclick="' . $html . '"';
        }

        return $html;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param int $index
     * @param string $list
     * @param string $listId
     * @return string
     */
    public function getProductClickHtml($product, $index = 0, $list = '', $listId = '')
    {
        $productClickBlock = $this->createBlock('Core', 'product_click.phtml');
        $html = '';

        if ($productClickBlock) {
            $productClickBlock->setProduct($product);
            $productClickBlock->setIndex($index);

            /**
             * If a list value is set use that one, if nothing add one
             */
            if (!$list) {
                $currentCategory = $this->getCurrentCategory();
                if (!empty($currentCategory)) {
                    $list = $this->getGtmCategory($currentCategory);
                } else {
                    /* Check if it is from a listing from search or advanced search*/
                    $requestPath = $this->_request->getModuleName() .
                        DIRECTORY_SEPARATOR . $this->_request->getControllerName() .
                        DIRECTORY_SEPARATOR . $this->_request->getActionName();
                    switch ($requestPath) {
                        case 'catalogsearch/advanced/result':
                            $list = __('Advanced Search Result');
                            break;
                        case 'catalogsearch/result/index':
                            $list = __('Search Result');
                            break;
                    }
                }
            }
            if (!$listId) {
                $currentCategory = $this->getCurrentCategory();
                if (!empty($currentCategory)) {
                    $listId = $currentCategory->getId();
                } else {
                    /* Check if it is from a listing from search or advanced search*/
                    $requestPath = $this->_request->getModuleName() .
                        DIRECTORY_SEPARATOR . $this->_request->getControllerName() .
                        DIRECTORY_SEPARATOR . $this->_request->getActionName();
                    switch ($requestPath) {
                        case 'catalogsearch/advanced/result':
                            $listId = 'advanced_search_result';
                            break;
                        case 'catalogsearch/result/index':
                            $listId = 'search_result';
                            break;
                    }
                }
            }
            $productClickBlock->setList($list);
            $productClickBlock->setListId($listId);
            $html = trim($productClickBlock->toHtml());

            return $html;
        }
    }

    /**
     * Returns category tree path
     * @param \Magento\Catalog\Model\Category $category
     * @return string
     */
    public function getGtmCategory($category)
    {
        $categoryPath = $category->getData('path');
        $this->_populateStoreCategories();

        return str_replace('{#}', '/', $this->_buildCategoryPath($categoryPath));
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @retrun array
     */
    public function getGA4Categories($category)
    {
        $ga4Categories = [];
        $categoryPath = $category->getData('path');
        $this->_populateStoreCategories();

        $categoryImploded = $this->_buildCategoryPath($categoryPath);
        $categories = explode("{#}", $categoryImploded);

        $index = 1;
        $categNameSuffix = '';
        foreach ($categories as $categName) {
            if ($index > 1) {
                $categNameSuffix = $index;
            }
            $categKey = 'item_category' . $categNameSuffix;
            $ga4Categories[$categKey] = $categName;
            $index +=1;
        }

        return $ga4Categories;
    }

    /**
     * Returns category tree path
     * @param array $categoryIds
     * @return string
     */
    public function getGA4CategoriesFromCategoryIds($categoryIds)
    {
        if (!count($categoryIds)) {
            return [];
        }

        if (empty($this->storeCategories)) {
            $this->_populateStoreCategories();
        }

        $categoryIds = $this->_filterStoreCategories($categoryIds);
        $categoryId = $categoryIds[0] ?? $this->rootCategoryId;

        $categoryPath = '';
        if (isset($this->storeCategories[$categoryId])) {
            $categoryPath = $this->storeCategories[$categoryId]['path'];
        }

        $ga4Categories = [];
        $categoryImploded = $this->_buildCategoryPath($categoryPath);
        $categories = explode("{#}", $categoryImploded);

        $index = 1;
        $categNameSuffix = '';
        foreach ($categories as $categName) {
            if ($index > 1) {
                $categNameSuffix =  $index;
            }
            $categKey = 'item_category' . $categNameSuffix;
            $ga4Categories[$categKey] = $categName;
            $index +=1;
        }

        return $ga4Categories;
    }

    /**
     * @return string
     */
    public function getDimensionsActionUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl() . 'weltpixel_gtm/index/dimensions';
    }

    /**
     * @param boolean $index
     * @return boolean
     */
    public function trackCustomAttribute($index)
    {
        return $this->_gtmOptions['general']['track_custom_attribute_' . $index];
    }

    /**
     * @param boolean $index
     * @return string
     */
    public function getCustomAttributeCode($index)
    {
        return $this->_gtmOptions['general']['track_custom_attribute_' . $index . '_code'];
    }

    /**
     * @param boolean $index
     * @return string
     */
    public function getCustomAttributeName($index)
    {
        return $this->_gtmOptions['general']['track_custom_attribute_' . $index . '_name'];
    }

    /**
     * @param integer $index
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getCustomAttributeValue($index, $product)
    {
        $attributeValue = '';
        if ($this->trackCustomAttribute($index)) {
            $attributeCode = $this->getCustomAttributeCode($index);
            try {
                $frontendValue = $product->getAttributeText($attributeCode);
                if (is_array($frontendValue)) {
                    $attributeValue = implode(',', $product->getAttributeText($attributeCode));
                } else {
                    $attributeValue = trim($product->getAttributeText($attributeCode));
                }
                if (!$attributeValue) {
                    $attributeValue = $product->getData($attributeCode);
                }
            } catch (\Exception $e) {
            }
        }

        return $attributeValue ?? '';
    }

    /**
    * @return bool
    */
    public function isDevMoveJsBottomEnabled()
    {
        return !$this->_request->isAjax() && $this->scopeConfig->isSetFlag(self::XML_PATH_DEV_MOVE_JS_TO_BOTTOM, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return array
     */
    public function getGA4CheckoutPaymentData()
    {
        return $this->checkoutSession->getGA4CheckoutPaymentData() ?? [];
    }

    /**
     * @return void
     */
    public function invalidateGA4CheckoutPaymentData()
    {
        $this->checkoutSession->setGA4CheckoutPaymentData(null);
    }

    /**
     * @return array
     */
    public function getGA4MultipleCheckoutPaymentData()
    {
        return $this->checkoutSession->getGA4MultipleCheckoutPaymentData() ?? [];
    }

    /**
     * @param array $multipleCheckoutPaymentData
     * @return void
     */
    public function setGA4MultipleCheckoutPaymentData($multipleCheckoutPaymentData)
    {
        $currentMultipleCheckoutPaymentData = $this->getGA4MultipleCheckoutPaymentData();
        $currentMultipleCheckoutPaymentData[] = $multipleCheckoutPaymentData;

        $this->checkoutSession->setGA4MultipleCheckoutPaymentData($currentMultipleCheckoutPaymentData);
    }

    /**
     * @return array|mixed|null
     */
    public function popG4MultipleCheckoutPaymentData()
    {
        $currentMultipleCheckoutPaymentData = $this->getGA4MultipleCheckoutPaymentData();
        if ($currentMultipleCheckoutPaymentData) {
            $ga4MultipleCheckoutPaymentData = array_shift($currentMultipleCheckoutPaymentData);
            $this->checkoutSession->setGA4MultipleCheckoutPaymentData($currentMultipleCheckoutPaymentData);
            return $ga4MultipleCheckoutPaymentData[0];
        }

        return [];
    }

    /**
     * @param string $storageKey
     * @param string $storageValue
     * @return void
     */
    public function setStorageData($storageKey, $storageValue)
    {
        $this->storage->setData($storageKey, $storageValue);
    }

    /**
     * @return array|mixed|null
     */
    public function getStorageData()
    {
        return $this->storage->getData();
    }

    /**
     * @return void
     */
    public function unsetStorageData()
    {
        $this->storage->unsetData();
    }

    /**
     * @param $dataLayerData
     * @return $this
     */
    public function setAdditionalDataLayerData($dataLayerData) {
        $additionalDataLayerData = $this->storage->getData('additional_datalayer_option');
        if (!$additionalDataLayerData) {
            $additionalDataLayerData = [];
        }
        $additionalDataLayerData[] = $dataLayerData;
        $this->storage->setData('additional_datalayer_option', $additionalDataLayerData);
        return $this;
    }

    /**
     * @param integer $customerId
     * @return integer
     */
    public function getCustomerOrderCount($customerId)
    {
        $customerPurchaseDaylapse = $this->getCustomerPurchaseDayLapse();
        $connection = $this->resourceConnection->getConnection();
        $date = date('Y-m-d H:i:s', strtotime("-$customerPurchaseDaylapse days"));

        $tableName = $this->resourceConnection->getTableName('sales_order');
        $query = "
        SELECT COUNT(*) AS total_orders
        FROM `" . $tableName . "`
        WHERE `created_at` > '" . $date . "' AND `customer_id` = " . $customerId;

        $result = $connection->fetchOne($query);
        return $result;
    }

    /**
     * @return boolean
     */
    public function isDatalayerPreviewEnabled()
    {
        return $this->_gtmOptions['general']['enable_datalayer_preview'];
    }

    /**
     * @return string
     */
    public function getDatalayerPreviewIpRestrictions()
    {
        return $this->_gtmOptions['general']['datalayer_preview_ip_addresses'] ?? '';
    }

    /**
     * @return boolean
     */
    public function isDatalayerPreviewIpRestrictionEnabled()
    {
        $remoteIpAddress = $this->_remoteAddress->getRemoteAddress();
        $ipRestrictions = $this->getDatalayerPreviewIpRestrictions();
        if ($ipRestrictions) {
            $ipRestrictionsArray = explode(',', $ipRestrictions);
            $ipRestrictionsArray = array_map('trim', $ipRestrictionsArray);
            return in_array($remoteIpAddress, $ipRestrictionsArray);
        }

        return true;
    }

    /**
     * @return boolean
     */
    public function isSmileElasticSuiteEnabled()
    {
        return $this->_moduleManager->isEnabled('Smile_ElasticsuiteCore');
    }

    /**
     * @return boolean
     */
    public function isLoadListingBlockEnabled()
    {
        return $this->_gtmOptions['general']['loadlistingblock'];
    }
}
