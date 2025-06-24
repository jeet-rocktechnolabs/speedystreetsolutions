<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Model\ApplyValidator;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;

class CategorySettingsValidation implements HidePriceValidationInterface
{
    /**
     * @var CategorySettingsProvider
     */
    private $categorySettings;

    public function __construct(CategorySettingsProvider $categorySettings)
    {
        $this->categorySettings = $categorySettings;
    }

    /**
     * @param Product $product
     */
    public function isHidePrice(ProductInterface $product): ?bool
    {
        foreach ($product->getCategoryIds() as $categoryId) {
            $categorySettings = $this->categorySettings->get((int)$categoryId);
            if ($categorySettings === null) {
                continue;
            }

            return !$categorySettings[CategorySettingsProvider::KEY_MODE];
        }

        return null;
    }
}
