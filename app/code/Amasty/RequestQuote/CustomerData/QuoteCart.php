<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\CustomerData;

use Amasty\RequestQuote\Model\UrlResolver;
use Magento\Customer\CustomerData\SectionSourceInterface;

class QuoteCart extends \Magento\Framework\DataObject implements SectionSourceInterface
{
    /**
     * @var \Amasty\RequestQuote\Model\Quote\Session
     */
    private $quoteSession;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Url
     */
    private $catalogUrl;

    /**
     * @var \Amasty\RequestQuote\Model\Cart
     */
    private $quoteCart;

    /**
     * @var \Magento\Checkout\CustomerData\ItemPoolInterface
     */
    private $itemPoolInterface;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    private $layout;

    /**
     * @var \Magento\Quote\Model\Quote|null
     */
    protected $quote = null;

    /**
     * @var int|float
     */
    protected $summeryCount;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    private $checkoutHelper;

    /**
     * @var \Magento\Tax\Block\Item\Price\Renderer
     */
    private $itemPriceRenderer;

    /**
     * @var \Amasty\RequestQuote\Model\HidePrice\Provider
     */
    private $hidePriceProvider;

    /**
     * @var UrlResolver
     */
    private $urlResolver;

    public function __construct(
        \Amasty\RequestQuote\Model\Quote\Session $quoteSession,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
        \Amasty\RequestQuote\Model\Cart $quoteCart,
        \Magento\Checkout\CustomerData\ItemPoolInterface $itemPoolInterface,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Tax\Block\Item\Price\Renderer $itemPriceRenderer,
        \Amasty\RequestQuote\Model\HidePrice\Provider $hidePriceProvider,
        UrlResolver $urlResolver,
        array $data = []
    ) {
        parent::__construct($data);
        $this->quoteSession = $quoteSession;
        $this->catalogUrl = $catalogUrl;
        $this->quoteCart = $quoteCart;
        $this->itemPoolInterface = $itemPoolInterface;
        $this->layout = $layout;
        $this->checkoutHelper = $checkoutHelper;
        $this->itemPriceRenderer = $itemPriceRenderer;
        $this->hidePriceProvider = $hidePriceProvider;
        $this->urlResolver = $urlResolver;
    }
    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $totals = $this->getQuote()->getTotals();
        $subtotalAmount = $totals['subtotal']->getValue();
        return [
            'summary_count' => $this->getSummaryCount(),
            'subtotalAmount' => $subtotalAmount,
            'subtotal' => isset($totals['subtotal'])
                ? $this->checkoutHelper->formatPrice($subtotalAmount)
                : 0,
            'items' => $this->getRecentItems(),
            'extra_actions' => $this->layout->createBlock(\Magento\Catalog\Block\ShortcutButtons::class)->toHtml(),
            'website_id' => $this->getQuote()->getStore()->getWebsiteId()
        ];
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->quoteSession->getQuote();
        }
        return $this->quote;
    }

    /**
     * @return int|float
     */
    protected function getSummaryCount()
    {
        if (!$this->summeryCount) {
            $this->summeryCount = $this->quoteCart->getSummaryQty() ?: 0;
        }
        return $this->summeryCount;
    }

    /**
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    protected function getRecentItems()
    {
        $items = [];
        if (!$this->getSummaryCount()) {
            return $items;
        }

        /* @var $item \Magento\Quote\Model\Quote\Item */
        foreach (array_reverse($this->getAllQuoteItems()) as $item) {
            $product = $item->getProduct();
            if (!$product->isVisibleInSiteVisibility()) {
                $product =  $item->getOptionByCode('product_type') !== null
                    ? $item->getOptionByCode('product_type')->getProduct()
                    : $item->getProduct();

                $products = $this->catalogUrl->getRewriteByProductStore([$product->getId() => $item->getStoreId()]);
                if (!isset($products[$product->getId()])) {
                    continue;
                }
                $urlDataObject = new \Magento\Framework\DataObject($products[$product->getId()]);
                $item->getProduct()->setUrlDataObject($urlDataObject);
            }

            $configureQuoteUrl = $this->urlResolver->getConfigureUrl((int) $item->getId(), (int) $item->getProductId());

            $itemData = $this->itemPoolInterface->getItemData($item);
            $itemData['configure_url'] = $configureQuoteUrl;
            $itemData['product_price'] = '';
            if (!$this->hidePriceProvider->isHidePrice($product) || $item->getCustomPrice() > 0) {
                $this->itemPriceRenderer->setItem($item);
                $this->itemPriceRenderer->setTemplate('checkout/cart/item/price/sidebar.phtml');
                $itemData['product_price'] = $this->itemPriceRenderer->toHtml();
            }
            $items[] = $itemData;
        }
        return $items;
    }

    /**
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    protected function getAllQuoteItems()
    {
        return $this->getQuote()->getAllVisibleItems();
    }
}
