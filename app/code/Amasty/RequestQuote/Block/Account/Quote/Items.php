<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Account\Quote;

use Amasty\Base\Model\Serializer;
use Amasty\RequestQuote\Api\Data\QuoteItemInterface;
use Amasty\RequestQuote\Model\Registry;
use Amasty\RequestQuote\Model\RegistryConstants;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Amasty\RequestQuote\Model\Source\Status;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order;

class Items extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * @var int
     */
    private $itemsPerPage;

    /**
     * @var \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    private $itemCollection;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var Order|null
     */
    private $order;

    /**
     * @var PostHelper
     */
    private $postHelper;

    /**
     * @var \Magento\Tax\Model\Config
     */
    private $taxConfig;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Magento\Framework\Pricing\Adjustment\Calculator
     */
    private $calculator;

    public function __construct(
        TemplateContext $context,
        Registry $registry,
        Serializer $serializer,
        DataObjectFactory $dataObjectFactory,
        OrderFactory $orderFactory,
        PostHelper $postHelper,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Pricing\Adjustment\Calculator $calculator,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->serializer = $serializer;
        $this->dataObjectFactory = $dataObjectFactory;
        parent::__construct($context, $data);
        $this->orderFactory = $orderFactory;
        $this->postHelper = $postHelper;
        $this->taxConfig = $taxConfig;
        $this->priceCurrency = $priceCurrency;
        $this->calculator = $calculator;
    }

    /**
     * @return \Amasty\RequestQuote\Model\Quote
     */
    public function getQuote()
    {
        return $this->coreRegistry->registry(RegistryConstants::AMASTY_QUOTE);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->itemsPerPage = (int)$this->_scopeConfig->getValue('sales/orders/items_per_page');

        $this->itemCollection = $this->getQuote()->getItemsCollection(false);
        $this->itemCollection->addFieldToFilter('parent_item_id', ['null' => 'parent_item_id']);
        /** @var \Magento\Theme\Block\Html\Pager $pagerBlock */
        $pagerBlock = $this->getChildBlock('amasty_quote_item_pager');
        if ($pagerBlock) {
            $pagerBlock->setLimit($this->itemsPerPage);
            //here pager updates collection parameters
            $pagerBlock->setCollection($this->itemCollection);
            $pagerBlock->setAvailableLimit([$this->itemsPerPage]);
            $pagerBlock->setShowAmounts($this->isPagerDisplayed());
        }

        foreach ($this->getItems() as $item) {
            if ($itemNotes = $this->serializer->unserialize($item->getAdditionalData())) {
                $itemNotes = $this->dataObjectFactory->create(['data' => $itemNotes]);
                $item->setNotes($itemNotes);
                $item->setHidePrice($itemNotes[QuoteItemInterface::HIDE_ORIGINAL_PRICE] ?? false);
            }
            $this->updateItemPrices($item, $itemNotes);
        }

        return parent::_prepareLayout();
    }

    /**
     * @param Item $item
     * @param DataObject $itemNotes
     */
    protected function updateItemPrices($item, $itemNotes)
    {
        $approvedPrice = $item->getPrice();
        $originalPrice = $this->getOriginalPrice($item);
        $discountAmount = ($originalPrice - $approvedPrice) * $item->getQty();
        if ($originalPrice) {
            $discountPercent = ($originalPrice - $approvedPrice) / $originalPrice * 100;
        } else {
            $discountPercent = 0;
        }
        $item->setQuoteDiscountAmount($discountAmount);
        $item->setQuoteDiscountPercent($discountPercent);
    }

    /**
     * @param Item $item
     * @return float
     */
    protected function getOriginalPrice($item)
    {
        return $this->calculator->getAmount(
            $item->getProduct()->getFinalPrice(),
            $item->getProduct()
        )->getValue(['tax', 'weee_tax']);
    }

    /**
     * @return bool
     */
    public function isPagerDisplayed()
    {
        $pagerBlock = $this->getChildBlock('amasty_quote_item_pager');
        return $pagerBlock && ($this->itemCollection->getSize() > $this->itemsPerPage);
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        /** @var \Magento\Theme\Block\Html\Pager $pagerBlock */
        $pagerBlock = $this->getChildBlock('amasty_quote_item_pager');
        return $pagerBlock ? $pagerBlock->toHtml() : '';
    }

    /**
     * @return \Magento\Framework\DataObject[]
     */
    public function getItems()
    {
        return $this->itemCollection->getItems();
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function getMoveUrl($params = [])
    {
        return $this->_urlBuilder->getUrl('amasty_quote/move/inCart', array_merge([
            'quote_id' => $this->getQuote()->getId()
        ], $params));
    }

    /**
     * @return bool
     */
    public function isMoveShowed()
    {
        return $this->getQuote()->getStatus() == Status::APPROVED;
    }

    /**
     * @return bool
     */
    public function isQuoteComplete()
    {
        return $this->getQuote()->getStatus() == Status::COMPLETE;
    }

    /**
     * @return bool
     */
    public function isApprovedPriceShowed()
    {
        return in_array($this->getQuote()->getStatus(), [Status::APPROVED, Status::EXPIRED, Status::COMPLETE]);
    }

    /**
     * @return Order|null
     */
    public function getOrder()
    {
        if ($this->order === null && $this->getQuote()->getStatus() == Status::COMPLETE) {
            /** @var Order $order */
            $this->order = $this->orderFactory->create();
            $this->order->loadByIncrementId($this->getQuote()->getReservedOrderId());
        }

        return $this->order;
    }

    /**
     * @return string
     */
    public function getOrderViewUrl()
    {
        return $this->getUrl('sales/order/view', ['order_id' => $this->getOrder()->getId()]);
    }

    /**
     * @param string $url
     * @return string
     */
    public function getPostData($url)
    {
        return $this->postHelper->getPostData($url);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareItem(\Magento\Framework\View\Element\AbstractBlock $renderer)
    {
        $renderer->setIsApprovedPriceShowed($this->isApprovedPriceShowed());

        return parent::_prepareItem($renderer);
    }
}
