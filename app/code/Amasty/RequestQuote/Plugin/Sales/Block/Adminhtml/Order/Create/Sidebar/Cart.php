<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Plugin\Sales\Block\Adminhtml\Order\Create\Sidebar;

use Magento\Catalog\Model\Product;
use Magento\Sales\Block\Adminhtml\Order\Create\Sidebar\Cart as MagentoCartSidebar;

class Cart
{
    /**
     * @var \Magento\Quote\Model\Quote\Item
     */
    private $item;

    /**
     * @param MagentoCartSidebar $subject
     * @param $item
     * @return array
     */
    public function beforeGetProduct(MagentoCartSidebar $subject, $item)
    {
        $this->item = $item;
        return [$item];
    }

    /**
     * @param MagentoCartSidebar $subject
     * @param callable $proceed
     * @param Product $product
     * @return string
     */
    public function aroundGetItemPrice(MagentoCartSidebar $subject, callable $proceed, Product $product)
    {
        return $subject->formatPrice($this->item->getCalculationPriceOriginal());
    }
}
