<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Plugin\Catalog\Model\Option;

use Amasty\HidePrice\Model\IsNeedRenderPrice;
use Magento\Catalog\Model\Product\Option\Value as OptionValue;
use Magento\Framework\Pricing\Render as PricingRender;

class Value
{
    /**
     * @var IsNeedRenderPrice
     */
    private $isNeedRenderPrice;

    public function __construct(
        IsNeedRenderPrice $isNeedRenderPrice
    ) {
        $this->isNeedRenderPrice = $isNeedRenderPrice;
    }

    public function afterGetPrice(
        OptionValue $subject,
        $result
    ) {
        if (!$this->isNeedRenderPrice->execute(
            $subject->getOption()->getProduct(),
            ['zone' => PricingRender::ZONE_ITEM_OPTION]
        ) && !$subject->getOption()->getDisableHideprice()) {
            return '0';
        }

        return $result;
    }
}
