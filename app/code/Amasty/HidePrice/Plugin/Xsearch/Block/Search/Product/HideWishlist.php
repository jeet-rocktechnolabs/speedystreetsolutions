<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Plugin\Xsearch\Block\Search\Product;

use Amasty\HidePrice\Helper\Data;
use Amasty\HidePrice\Model\ConfigProvider;
use Amasty\HidePrice\Model\Xsearch\HidePopupData;
use Amasty\Xsearch\Block\Search\Product as ProductBlock;
use Magento\Catalog\Model\ProductRepository;

/**
 * @see \Amasty\Xsearch\Block\Search\Product::isWishlistAllowed()
 */
class HideWishlist
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundIsWishlistAllowed(ProductBlock $subject, callable $proceed, array $productData): bool
    {
        $isCompareAllowed = $productData[HidePopupData::IS_WISHLIST_ALLOWED] ?? true;

        return $isCompareAllowed && $proceed();
    }
}
