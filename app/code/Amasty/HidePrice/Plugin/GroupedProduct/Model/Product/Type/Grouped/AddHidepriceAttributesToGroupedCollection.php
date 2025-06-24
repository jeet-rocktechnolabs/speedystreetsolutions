<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Plugin\GroupedProduct\Model\Product\Type\Grouped;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

class AddHidepriceAttributesToGroupedCollection
{
    public function afterGetAssociatedProductCollection(Grouped $subject, Collection $collection): Collection
    {
        $collection->addAttributeToSelect(['am_hide_price_mode', 'am_hide_price_customer_gr']);

        return $collection;
    }

    public function afterGetAssociatedProducts(Grouped $subject, array $result, Product $product): array
    {
        if ($product->getAmHidePriceMode() === '0') {
            foreach ($result as $item) {
                $item->setData('am_hide_price_mode', '0');
                $item->setData('am_hide_price_customer_gr', $product->getAmHidePriceCustomerGr());
            }
        }

        return $result;
    }
}
