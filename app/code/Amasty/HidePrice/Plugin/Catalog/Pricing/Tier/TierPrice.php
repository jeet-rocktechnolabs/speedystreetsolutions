<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Plugin\Catalog\Pricing\Tier;

use Magento\Catalog\Pricing\Price\TierPrice as NativeTierPrice;
use Magento\Framework\Pricing\Amount\AmountInterface;

class TierPrice
{
    /**
     * @param NativeTierPrice $subject
     * @param \Closure $proceed
     * @param AmountInterface $amount
     *
     * @return int|mixed
     */
    public function aroundGetSavePercent(
        NativeTierPrice $subject,
        \Closure $proceed,
        AmountInterface $amount
    ) {
        $result = 0;
        if ($subject->getProduct()
            && $subject->getProduct()->getPriceInfo()->getPrice('final_price')->getAmount()->getValue()
        ) {
            $result = $proceed($amount);;
        }

        return $result;
    }
}
