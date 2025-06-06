<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Model\ApplyValidator;

use Amasty\HidePrice\Helper\Data;
use Amasty\HidePrice\Model\ConfigProvider;
use Magento\Catalog\Api\Data\ProductInterface;

class GlobalConfigurationValidation implements HidePriceValidationInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CustomerGroupResolver
     */
    private $groupResolver;

    public function __construct(ConfigProvider $configProvider, CustomerGroupResolver $groupResolver)
    {
        $this->configProvider = $configProvider;
        $this->groupResolver = $groupResolver;
    }

    public function isHidePrice(ProductInterface $product): ?bool
    {
        $settingCustomerGroup = $this->configProvider->getHideForCustomerGroups();
        if (in_array($this->groupResolver->get(), $settingCustomerGroup)) {
            $productCategories = $product->getCategoryIds();
            $settingCategories = $this->configProvider->getHideForCategoryIds();

            //check for root category - hide price for all
            return in_array(Data::ROOT_CATEGORY_ID, $settingCategories)
            || count(array_uintersect($productCategories, $settingCategories, "strcmp")) > 0;
        }

        return null;
    }
}
