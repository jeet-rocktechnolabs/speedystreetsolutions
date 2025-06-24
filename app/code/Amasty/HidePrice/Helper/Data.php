<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Helper;

use Amasty\HidePrice\Model\Source\HideButton;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\DataObject;
use Amasty\HidePrice\Model\Source\ReplaceButton;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const ROOT_CATEGORY_ID = 1;
    public const NOT_LOGGED_KEY = '00';
    public const DISABLED_GROUP_KEY = -1;
    public const HIDE_PRICE_DATA_ROLE = 'data-role="amhideprice-hide-button"';
    public const HIDE_PRICE_POPUP_IDENTIFICATOR = 'AmastyHidePricePopup';

    /**
     * @var array
     */
    protected $cache = [];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;
    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $sessionFactory;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    private $filterManager;

    /**
     * @var \Magento\Customer\Model\Url
     */
    private $customerUrl;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        CollectionFactory $categoryCollectionFactory, // @deprecated
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Customer\Model\Url $customerUrl
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->jsonEncoder = $jsonEncoder;
        $this->sessionFactory = $sessionFactory;
        $this->filterManager = $filterManager;
        $this->customerUrl = $customerUrl;
    }

    private function getCustomerSession()
    {
        return $this->sessionFactory->create();
    }

    public function getModuleConfig(string $path): string
    {
        return (string)$this->scopeConfig->getValue('amasty_hide_price/' . $path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isModuleEnabled()
    {
        return $this->getModuleConfig('general/enabled') && $this->isModuleOutputEnabled();
    }

    /**
     * @deprecad
     * @see \Amasty\HidePrice\Model\ApplyValidator::isHidePrice
     * @param ProductInterface $product
     * @return bool
     */
    public function isApplied(ProductInterface $product)
    {
        return ObjectManager::getInstance()->get(\Amasty\HidePrice\Model\ApplyValidator::class)->isHidePrice($product);
    }

    /**
     * Checking module setting and output
     * @param ProductInterface $product
     * @return bool
     */
    public function isNeedHideProduct(ProductInterface $product)
    {
        $isConfigEnabled = $this->getModuleConfig('information/hide_price')
            || $this->getHideAddToCart();
        return $isConfigEnabled && $this->isApplied($product);
    }

    /**
     * @deprecad
     * @see \Amasty\HidePrice\Model\ConfigProvider::isHideAddToCart
     * @return bool
     */
    public function getHideAddToCart()
    {
        return ObjectManager::getInstance()->get(\Amasty\HidePrice\Model\ConfigProvider::class)->isHideAddToCart();
    }

    /**
     * @deprecad
     * @see \Amasty\HidePrice\Model\ConfigProvider::isHideWishlist
     * @return bool
     */
    public function getHideWishlist()
    {
        return ObjectManager::getInstance()->get(\Amasty\HidePrice\Model\ConfigProvider::class)->isHideWishlist();
    }

    /**
     * Plugin in stock status must work
     *
     * @param $result
     * @param ProductInterface $product
     *
     * @return bool
     */
    public function checkStockStatus($result, ProductInterface $product)
    {
        return $result;
    }

    /**
     * Generate button html depend on module configuration
     * @param $product
     * @return string
     */
    public function getNewPriceHtmlBox($product)
    {
        $priceBox = $this->getPriceBox();

        // help for magento swatches detect category page
        $html = sprintf('<div class="%s" data-product-id="%d"></div>', $priceBox, $product->getId());
        $text = $this->filterManager->stripTags(
            $this->getModuleConfig('frontend/text'),
            [
                'allowableTags' => null,
                'escape' => true
            ]
        );
        $image = $this->getModuleConfig('frontend/image');
        if ($text || $image) {
            $href = $this->getModuleConfig('frontend/link');
            if ($href) {
                if ($href == self::HIDE_PRICE_POPUP_IDENTIFICATOR) {
                    $tag = $this->generatePopup($product);
                } else {
                    $href = $this->checkLoginUrl($href);
                    $tag = '<a href="' . $href . '" ';
                }
                $closeTag = '</a>';
            } else {
                $tag = '<div ';
                $closeTag = '</div>';
            }

            $customStyles = $this->getModuleConfig('frontend/custom_css');
            if ($customStyles) {
                $customStyles = 'style="' . $customStyles . '"';
            }
            $html .= $tag . ' class="amasty-hide-price-container" ' . $customStyles . '>';

            if ($image) {
                $mediaPath = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
                $image = $mediaPath . '/amasty/hide_price/' . $image;
                $html .= '<img class="amasty-hide-price-image" src="' . $image .'">';
            }

            if ($text) {
                $html .= '<span class="amasty-hide-price-text">' . $text . '</span>';
            }

            $html .= $closeTag;
        }

        return $html;
    }

    private function getPriceBox(): string
    {
        //fix dropdown in configurable product
        return $this->_request->getControllerName() !== 'product' ? 'price-box price-final_price'
            : 'price-final_price';
    }

    /**
     * @param string $href
     *
     * @return string|string
     */
    protected function checkLoginUrl(string $href)
    {
        if (strpos($href, 'customer/account/login') !== false) {
            $href = $this->customerUrl->getLoginUrl();
        }

        return $href;
    }

    /**
     * Generate button replacement html
     * @param Product|null $product
     * @param array|null $productData
     * @return string
     */
    public function getNewAddToCartHtml($product = null, $productData = null)
    {
        // help for magento swatches detect category page
        $result = '';

        if ($this->getModuleConfig('information/hide_button') == HideButton::REPLACE_WITH_NEW_ONE) {
            $text = strip_tags($this->getModuleConfig('information/replace_text'));
            $link = $this->getModuleConfig('information/replace_link') ?: '';
            if (!$product && isset($productData['id'])) {
                $product = new DataObject([
                    'id' => $productData['id'],
                    'name' => $productData['name']
                ]);
            }

            switch ($this->getModuleConfig('information/replace_with')) {
                case ReplaceButton::REDIRECT_URL:
                    $href = $this->checkLoginUrl((string)$this->getModuleConfig('information/redirect_link'));
                    $href = $href ?: '#';
                    $tag = '<a href="' . $href . '"';
                    break;
                case ReplaceButton::HIDE_PRICE_POPUP:
                case ReplaceButton::CUSTOM_FORM:
                    $tag = $this->generatePopup($product);
                    break;
            }

            $styles = strip_tags($this->getModuleConfig('information/replace_css'));
            $styles = $styles ? ' style="' . $styles . '"' : '';

            $result .= sprintf(
                '%s class="amasty-hide-price-button" %s><span>%s</span></a>',
                $tag,
                $styles,
                $text
            );
        }

        return $result;
    }

    /**
     * generate Js code for Get a Quote Form
     * @param Product|DataObject $product
     * @return string
     */
    private function generateFormJs($product)
    {
        $js = '<script>';
        $js .= 'require([
                "jquery",
                 "Amasty_HidePrice/js/amhidepriceForm"
            ], function ($, amhidepriceForm) {
                amhidepriceForm.addProduct(' . $this->generateFormConfig($product) . ');
            });';
        $js .= '</script>';

        return $js;
    }

    private function generateFormConfig($product)
    {
        $customer = $this->getCustomerSession()->getCustomer();
        return $this->jsonEncoder->encode([
            'url' => $this->_getUrl('amasty_hide_price/request/add'),
            'id' => $product->getId(),
            'name'   => $product->getName(),
            'customer' => [
                'name'  => $customer->getName(),
                'email' => $customer->getEmail(),
                'phone' => $customer->getPhone()
            ]
        ]);
    }

    /**
     * @return bool
     */
    public function isGDPREnabled()
    {
        return (bool)$this->getModuleConfig('gdpr/enabled');
    }

    /**
     * @return string
     */
    public function getGDPRText()
    {
        return $this->filterManager->stripTags(
            $this->getModuleConfig('gdpr/text'),
            [
                'allowableTags' => '<a>',
                'escape' => false
            ]
        );
    }

    /**
     * @param Product|DataObject $product
     * @return string
     */
    private function generatePopup($product)
    {
        $popupHtml = $this->generateFormJs($product)
            . '<a data-product-id="' . $product->getId() . '" data-amhide="'
            . self::HIDE_PRICE_POPUP_IDENTIFICATOR . '" ';

        return $popupHtml;
    }

    /**
     * @return bool
     */
    public function isCustomFormOn()
    {
        return $this->_moduleManager->isEnabled('Amasty_Customform');
    }

    /**
     * Checking module setting and output
     * @param ProductInterface $product
     * @return bool
     */
    public function isNeedHidePrice(ProductInterface $product)
    {
        return $this->getModuleConfig('information/hide_price') && $this->isApplied($product);
    }
}
