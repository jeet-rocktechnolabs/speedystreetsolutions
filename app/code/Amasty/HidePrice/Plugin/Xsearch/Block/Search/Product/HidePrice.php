<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Plugin\Xsearch\Block\Search\Product;

use Amasty\HidePrice\Model\ConfigProvider;
use Amasty\HidePrice\Model\Xsearch\HidePopupData;
use Amasty\Xsearch\Block\Search\Product as ProductBlock;

/**
 * @see \Amasty\Xsearch\Block\Search\Product::getProducts
 */
class HidePrice
{
    /**
     * @var HidePopupData
     */
    private $hidePopupData;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        HidePopupData $hidePopupData,
        ConfigProvider $configProvider
    ) {
        $this->hidePopupData = $hidePopupData;
        $this->configProvider = $configProvider;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetProducts(ProductBlock $subject, array $productsData): array
    {
        return $this->configProvider->isEnabled() ? $this->hidePopupData->execute($productsData) : $productsData;
    }
}
