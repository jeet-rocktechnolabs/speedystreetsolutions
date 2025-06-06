<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\HidePrice;

use Magento\Catalog\Api\Data\ProductInterface;

class Provider
{
    /**
     * @param ProductInterface $product
     * @return bool
     */
    public function isHidePrice(ProductInterface $product)
    {
        return false;
    }
}
