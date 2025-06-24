<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Plugin\Xsearch\Block\Search\Product;

use Amasty\HidePrice\Model\Xsearch\HidePopupData;
use Amasty\Xsearch\Block\Search\Product as ProductBlock;

/**
 * @see \Amasty\Xsearch\Block\Search\Product::isCompareAllowed()
 */
class HideCompare
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundIsCompareAllowed(ProductBlock $subject, callable $proceed, array $productData): bool
    {
        $isCompareAllowed = $productData[HidePopupData::IS_COMPARE_ALLOWED] ?? true;

        return $isCompareAllowed && $proceed();
    }
}
