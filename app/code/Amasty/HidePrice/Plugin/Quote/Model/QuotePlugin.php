<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Plugin\Quote\Model;

use Amasty\HidePrice\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;

class QuotePlugin
{
    /**
     * @var Data
     */
    private $helper;

    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param Quote $subject
     * @param Product $product
     * @param $request
     * @param $processMode
     *
     * @return array
     * @throws LocalizedException
     */
    public function beforeAddProduct(
        Quote $subject,
        Product $product,
        $request = null,
        $processMode = AbstractType::PROCESS_MODE_FULL
    ) {
        if (!$subject instanceof \Amasty\RequestQuote\Model\Quote
            && $this->helper->getHideAddToCart()
            && $this->helper->isApplied($product)
        ) {
            throw new LocalizedException(__('Adding to the cart is disabled'));
        }

        return [$product, $request, $processMode];
    }
}
