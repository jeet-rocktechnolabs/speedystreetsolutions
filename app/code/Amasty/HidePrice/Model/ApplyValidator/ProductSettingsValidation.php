<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Model\ApplyValidator;

use Magento\Catalog\Api\Data\ProductInterface;

class ProductSettingsValidation implements HidePriceValidationInterface
{
    public const PRODUCT_ATTR_MODE = 'am_hide_price_mode';

    public const PRODUCT_ATTR_CUSTOMER_GROUPS = 'am_hide_price_customer_gr';

    /**
     * @var CustomerGroupResolver
     */
    private $groupResolver;

    public function __construct(CustomerGroupResolver $groupResolver)
    {
        $this->groupResolver = $groupResolver;
    }

    public function isHidePrice(ProductInterface $product): ?bool
    {
        $mode = $product->getData(self::PRODUCT_ATTR_MODE);
        $customerGroups = $product->getData(self::PRODUCT_ATTR_CUSTOMER_GROUPS);

        if ($mode !== null && $customerGroups) {
            $customerGroups = $this->toArray($customerGroups);
            if (in_array($this->groupResolver->get(), $customerGroups, true)) {
                return !(bool)$mode;
            }
        }

        return null;
    }

    private function toArray($string): array
    {
        $string = str_replace(' ', '', (string) $string);

        return $string ? explode(',', $string) : [];
    }
}
