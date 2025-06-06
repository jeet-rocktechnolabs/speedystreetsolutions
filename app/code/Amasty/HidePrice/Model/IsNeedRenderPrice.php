<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Model;

use Amasty\HidePrice\Helper\Data;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Pricing\Render as PricingRender;
use Magento\Framework\Pricing\SaleableInterface;

class IsNeedRenderPrice
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Data
     */
    private $helper;

    public function __construct(
        ConfigProvider $configProvider,
        Data $helper
    ) {
        $this->configProvider = $configProvider;
        $this->helper = $helper;
    }

    public function execute(SaleableInterface $saleableItem, array $arguments): bool
    {
        // if Item not a product - show price
        $isNotProduct = !($saleableItem instanceof ProductInterface);
        // is current price block zone is not list, view or option
        $isNoZone = (key_exists('zone', $arguments)
            && !in_array(
                $arguments['zone'],
                [PricingRender::ZONE_ITEM_LIST, PricingRender::ZONE_ITEM_VIEW, PricingRender::ZONE_ITEM_OPTION]
            )
        );
        $isShowPrice = !$this->configProvider->isEnabled()
            || !$this->configProvider->isHidePrice()
            || $isNotProduct
            || $isNoZone
            || !$this->helper->isApplied($saleableItem);

        return $isShowPrice;
    }
}
