<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Model\Xsearch;

use Amasty\HidePrice\Helper\Data;
use Amasty\HidePrice\Model\ConfigProvider;
use Amasty\HidePrice\Model\IsNeedRenderPrice;
use Amasty\HidePrice\Plugin\Framework\Pricing\Render;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

class HidePopupData
{
    public const IS_COMPARE_ALLOWED = 'amhideprice_is_compare_allowed';
    public const IS_WISHLIST_ALLOWED = 'amhideprice_is_wishlist_allowed';
    public const IS_ADD_TO_CART_ALLOWED = 'amhideprice_is_add_to_cart_allowed';

    /**
     * @var Render
     */
    private $render;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var IsNeedRenderPrice
     */
    private $isNeedRenderPrice;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Render $render,
        ProductCollectionFactory $productCollectionFactory,
        IsNeedRenderPrice $isNeedRenderPrice,
        Data $helper,
        ConfigProvider $configProvider
    ) {
        $this->render = $render;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->isNeedRenderPrice = $isNeedRenderPrice;
        $this->helper = $helper;
        $this->configProvider = $configProvider;
    }

    public function execute(array $productsData): array
    {
        $ids = array_column($productsData, 'entity_id');
        $collection = $this->productCollectionFactory->create();
        $collection->addIdFilter($ids);
        foreach ($collection as $product) {
            $id = $product->getEntityId();
            if (!$this->isNeedRenderPrice->execute($product, [])) {
                $productsData[$id]['price'] = $this->helper->getNewPriceHtmlBox($product);
            }
            $isApplied = $this->helper->isApplied($product);
            $productsData[$id][self::IS_COMPARE_ALLOWED] = !($this->configProvider->isHideCompare() && $isApplied);
            $productsData[$id][self::IS_WISHLIST_ALLOWED] = !($this->configProvider->isHideWishlist() && $isApplied);
            $productsData[$id][self::IS_ADD_TO_CART_ALLOWED]
                = !($this->configProvider->isHideAddToCart() && $isApplied);
        }

        return $productsData;
    }
}
