<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Cart;

use Amasty\Base\Model\MagentoVersion;
use Amasty\RequestQuote\Helper\Data;
use Amasty\RequestQuote\Model\UrlResolver;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Customer\CustomerData\JsLayoutDataProviderPoolInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;

class Sidebar extends Template
{
    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * @var EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var MagentoVersion
     */
    private $magentoVersion;

    /**
     * @var UrlResolver
     */
    private $urlResolver;

    public function __construct(
        Context $context,
        JsLayoutDataProviderPoolInterface $jsLayoutDataProvider,
        ImageHelper $imageHelper,
        Data $helperData,
        EncoderInterface $jsonEncoder,
        MagentoVersion $magentoVersion,
        UrlResolver $urlResolver,
        array $data = []
    ) {
        $this->magentoVersion = $magentoVersion;
        $data = $this->modifyJsLayout($data, $jsLayoutDataProvider);
        parent::__construct($context, $data);
        $this->helperData = $helperData;
        $this->context = $context;
        $this->imageHelper = $imageHelper;
        $this->jsonEncoder = $jsonEncoder;
        $this->urlResolver = $urlResolver;
    }

    private function modifyJsLayout(array $data, JsLayoutDataProviderPoolInterface $jsLayoutDataProvider): array
    {
        if (isset($data['jsLayout'])) {
            $data['jsLayout'] = array_merge_recursive($jsLayoutDataProvider->getData(), $data['jsLayout']);
        } else {
            $data['jsLayout'] = $jsLayoutDataProvider->getData();
        }
        unset($data['jsLayout']['components']['minicart_content']);
        if (version_compare($this->magentoVersion->get(), '2.4.2', '<')) {
            $data['jsLayout']['components']['quotecart_content']['children']['item.renderer']['component']
                = 'uiComponent';
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getQuoteCartUrl()
    {
        return $this->urlResolver->getCartUrl();
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->helperData->isAllowedCustomerGroup() && $this->helperData->isActive();
    }

    /**
     * @return string
     */
    public function getSerializedConfig()
    {
        return $this->jsonEncoder->encode($this->getConfig());
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            // 'checkoutUrl' name need in sidebar.js for "My Quote Cart" button
            'checkoutUrl' => $this->getQuoteCartUrl(),
            'updateItemQtyUrl' => $this->getUpdateItemQtyUrl(),
            'removeItemUrl' => $this->getRemoveItemUrl(),
            'imageTemplate' => $this->getImageHtmlTemplate(),
            'baseUrl' => $this->getBaseUrl(),
            'minicartMaxItemsVisible' => $this->getMiniCartMaxItemsCount(),
            'websiteId' => $this->_storeManager->getStore()->getWebsiteId(),
            'maxItemsToDisplay' => $this->getMaxItemsToDisplay()
        ];
    }

    /**
     * @return string
     */
    public function getUpdateItemQtyUrl()
    {
        return $this->getUrl('amasty_quote/sidebar/updateItemQty', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * @return string
     */
    public function getRemoveItemUrl()
    {
        return $this->getUrl('amasty_quote/sidebar/removeItem', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * @return string
     */
    public function getImageHtmlTemplate()
    {
        return $this->imageHelper->getFrame()
            ? 'Magento_Catalog/product/image'
            : 'Magento_Catalog/product/image_with_borders';
    }

    /**
     * @return int
     */
    private function getMiniCartMaxItemsCount()
    {
        return (int)$this->_scopeConfig->getValue('checkout/sidebar/count', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return int
     */
    private function getMaxItemsToDisplay()
    {
        return (int)$this->_scopeConfig->getValue(
            'checkout/sidebar/max_items_display_count',
            ScopeInterface::SCOPE_STORE
        );
    }
}
