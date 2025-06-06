<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Plugin\Catalog\Block\Product\ProductList;

use Magento\Catalog\Block\Product\ListProduct;

class Category extends AbstractList
{
    /**
     * @param ListProduct $subject
     * @param $html
     * @return string
     */
    public function afterToHtml(ListProduct $subject, $html)
    {
        $html = $this->replaceButtonFromHtml($html);

        return $html;
    }
}
