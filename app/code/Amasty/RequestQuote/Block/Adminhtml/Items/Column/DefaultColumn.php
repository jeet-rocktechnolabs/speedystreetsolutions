<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Adminhtml\Items\Column;

use Magento\Quote\Model\Quote\Item\AbstractItem as QuoteItem;

class DefaultColumn extends \Amasty\RequestQuote\Block\Adminhtml\Items\AbstractItems
{
    /**
     * @var \Magento\Catalog\Model\Product\OptionFactory
     */
    private $optionFactory;

    /**
     * @var \Magento\Catalog\Helper\Product\Configuration
     */
    private $productConfig;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Amasty\RequestQuote\Model\Quote\Backend\Session $quoteSession,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Catalog\Model\Product\OptionFactory $optionFactory,
        \Magento\Catalog\Helper\Product\Configuration $productConfig,
        \Magento\Tax\Model\Config $taxConfig,
        array $data = []
    ) {
        $this->optionFactory = $optionFactory;
        $this->productConfig = $productConfig;
        parent::__construct(
            $context,
            $stockRegistry,
            $stockConfiguration,
            $quoteSession,
            $currencyFactory,
            $taxConfig,
            $data
        );
    }

    /**
     * @return QuoteItem
     */
    public function getItem()
    {
        $item = $this->_getData('item');
        if ($item instanceof Item || $item instanceof QuoteItem) {
            return $item;
        } else {
            return $item->getOrderItem();
        }
    }

    /**
     * @return array
     */
    public function getOrderOptions()
    {
        /* @var $helper \Magento\Catalog\Helper\Product\Configuration */
        $helper = $this->productConfig;
        return $helper->getCustomOptions($this->getItem());
    }

    /**
     * @param array $optionInfo
     * @return string
     */
    public function getCustomizedOptionValue($optionInfo)
    {
        $default = isset($optionInfo['value']) ? $optionInfo['value'] : '';
        if (isset($optionInfo['option_type'])) {
            try {
                $group = $this->optionFactory->create()->groupFactory($optionInfo['option_type']);
                return $group->getCustomizedView($optionInfo);
            } catch (\Exception $e) {
                return $default;
            }
        }
        return $default;
    }

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->getItem()->getSku();
    }

    /**
     * @param QuoteItem $item
     * @return mixed
     */
    public function getTotalAmount($item)
    {
        return $item->getRowTotal() - $item->getDiscountAmount();
    }

    /**
     * @param QuoteItem $item
     * @return mixed
     */
    public function getBaseTotalAmount($item)
    {
        return $item->getBaseRowTotal() - $item->getBaseDiscountAmount();
    }
}
