<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Plugin\Catalog\Block\Product\ProductList;

use Magento\CatalogWidget\Block\Product\ProductsList;

class Widget extends AbstractList
{
    /**
     * @param ProductsList $subject
     * @param $html
     * @return string
     */
    public function afterToHtml(ProductsList $subject, $html)
    {
        $html = $this->replaceButtonFromHtml($html);

        return $html;
    }
}
