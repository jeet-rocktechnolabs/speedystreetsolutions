<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Plugin\GiftCard\Ui\DataProvider\Product\Collector\Price;

use Amasty\HidePrice\Model\ApplyValidator;
use Amasty\HidePrice\Model\ConfigProvider;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Framework\Registry;
use Magento\GiftCard\Ui\DataProvider\Product\Collector\Price as GiftCardPriceRenderer;

class HidePrice
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ApplyValidator
     */
    private $applyValidator;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(ConfigProvider $configProvider, ApplyValidator $applyValidator, Registry $registry)
    {
        $this->configProvider = $configProvider;
        $this->applyValidator = $applyValidator;
        $this->registry = $registry;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCollect(
        GiftCardPriceRenderer $subject,
        callable $proceed,
        ProductInterface $product,
        ProductRenderInterface $productRender
    ): void {
        if ($this->configProvider->isHidePrice($productRender->getStoreId())
            && !$this->registry->registry('hideprice_off')
            && $this->applyValidator->isHidePrice($product)
        ) {
            return;
        }

        $proceed($product, $productRender);
    }
}
