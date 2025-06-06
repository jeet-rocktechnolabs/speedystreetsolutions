<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Traits\Account\Quote;

use Magento\Checkout\Block\Cart\Item\Renderer;
use Magento\Quote\Model\Quote\Item\AbstractItem;

trait PriceRenderer
{
    /**
     * @param AbstractItem $item
     * @return string
     */
    public function getOriginalPriceHtml(AbstractItem $item)
    {
        /** @var Renderer $block */
        $block = $this->getLayout()->getBlock('checkout.item.price.original');
        $block->setItem($item);
        return $block->toHtml();
    }

    /**
     * @param AbstractItem $item
     * @return string
     */
    public function getDiscountPriceHtml(AbstractItem $item)
    {
        /** @var Renderer $block */
        $block = $this->getLayout()->getBlock('checkout.item.price.discount');
        $block->setItem($item);
        return $block->toHtml();
    }

    /**
     * @param AbstractItem $item
     * @return string
     */
    public function getRequestedPriceHtml(AbstractItem $item)
    {
        /** @var Renderer $block */
        $block = $this->getLayout()->getBlock('checkout.item.price.requested');
        $block->setItem($item);
        return $block->toHtml();
    }
}
