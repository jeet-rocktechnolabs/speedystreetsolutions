<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Model;

use Amasty\Base\Model\ConfigProviderAbstract;

class ConfigProvider extends ConfigProviderAbstract
{
    private const ENABLE = 'general/enabled';
    private const HIDE_WISHLIST = 'information/hide_wishlist';
    private const HIDE_COMPARE = 'information/hide_compare';
    private const HIDE_ADD_TO_CART = 'information/hide_button';
    private const HIDE_PRICE = 'information/hide_price';
    private const REPLACE_BUTTON_TYPE = 'information/replace_with';
    private const CUSTOM_FORM_ID = 'information/custom_form';
    private const HIDE_PRICE_LINK = 'frontend/link';
    private const IS_GDPR_ENABLED = 'gdpr/enabled';
    private const GDPR_TEXT = 'gdpr/text';

    /**
     * @var string
     */
    protected $pathPrefix = 'amasty_hide_price/';

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->isSetFlag(self::ENABLE, $storeId);
    }

    public function isHideWishlist(?int $storeId = null): bool
    {
        return $this->isSetFlag(self::HIDE_WISHLIST, $storeId);
    }

    public function isHideCompare(?int $storeId = null): bool
    {
        return $this->isSetFlag(self::HIDE_COMPARE, $storeId);
    }

    public function isHideAddToCart(?int $storeId = null): bool
    {
        return $this->isSetFlag(self::HIDE_ADD_TO_CART, $storeId);
    }

    public function getHideAddToCartValue(?int $storeId = null): int
    {
        return (int)$this->getValue(self::HIDE_ADD_TO_CART, $storeId);
    }

    public function isHidePrice(?int $storeId = null): bool
    {
        return $this->isSetFlag(self::HIDE_PRICE, $storeId);
    }

    public function getReplaceButtonType(?int $storeId = null): string
    {
        return (string)$this->getValue(self::REPLACE_BUTTON_TYPE, $storeId);
    }

    public function getCustomFormId(?int $storeId = null): int
    {
        return (int)$this->getValue(self::CUSTOM_FORM_ID, $storeId);
    }

    public function getHidePriceLink(?int $storeId = null): string
    {
        return (string)$this->getValue(self::HIDE_PRICE_LINK, $storeId);
    }

    public function isGDPREnabled(?int $storeId = null): bool
    {
        return (bool)$this->isSetFlag(self::IS_GDPR_ENABLED, $storeId);
    }

    public function getGDPRText(?int $storeId = null): string
    {
        return (string)$this->getValue(self::GDPR_TEXT, $storeId);
    }

    /**
     * @return string[]
     */
    public function getHideForCustomerGroups(?int $storeId = null): array
    {
        $value = $this->getValue('general/customer_group', $storeId);

        return $this->convertStringToArray($value);
    }

    /**
     * @return int[]
     */
    public function getHideForCategoryIds(?int $storeId = null): array
    {
        $value = $this->getValue('general/category', $storeId);

        return $this->convertStringToIntArray($value);
    }

    /**
     * @return int[]
     */
    public function getIgnoreProductIds(?int $storeId = null): array
    {
        $value = $this->getValue('general/ignore_products', $storeId);

        return $this->convertStringToIntArray($value);
    }

    /**
     * @return int[]
     */
    public function getIgnoreCustomerIds(?int $storeId = null): array
    {
        $value = $this->getValue('general/ignore_customer', $storeId);

        return $this->convertStringToIntArray($value);
    }

    /**
     * @return int[]
     */
    private function convertStringToIntArray(?string $string): array
    {
        return array_map('intval', $this->convertStringToArray($string));
    }

    /**
     * @return string[]
     */
    private function convertStringToArray(?string $string): array
    {
        $string = str_replace(' ', '', (string)$string);

        return $string ? explode(',', $string) : [];
    }
}
