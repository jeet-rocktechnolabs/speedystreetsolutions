<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Setup\Patch\Data;

use Amasty\HidePrice\Model\Source\Group;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CreateAttributes implements DataPatchInterface
{
    private const HIDE_PRICE_MODE_ATTR = 'am_hide_price_mode';
    private const HIDE_PRICE_CUSTOMER_GROUP_ATTR = 'am_hide_price_customer_gr';
    private const HIDE_PRICE_MODE_CAT_ATTR = 'am_hide_price_mode_cat';
    private const HIDE_PRICE_CUSTOMER_GROUP_CAT_ATTR = 'am_hide_price_customer_gr_cat';

    /**
     * @var EavSetup
     */
    private $eavSetup;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $setup
    ) {
        $this->eavSetup = $eavSetupFactory->create(['setup' => $setup]);
    }

    public function apply(): self
    {
        $this->addHidePriceModeAttr();
        $this->addHidePriceCustomerGroupAttr();
        $this->addHidePriceModeCatAttribute();
        $this->addHidePriceCustomerGrCatAttr();

        return $this;
    }

    private function addHidePriceCustomerGrCatAttr(): void
    {
        $this->eavSetup->addAttribute(
            Category::ENTITY,
            self::HIDE_PRICE_CUSTOMER_GROUP_CAT_ATTR,
            [
                'type' => 'text',
                'backend' => ArrayBackend::class,
                'label' => __('Use Current Price Mode By Customer Group'),
                'input' => 'multiselect',
                'required' => false,
                'sort_order' => 4,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'wysiwyg_enabled' => false,
                'is_html_allowed_on_front' => false,
                'group' => __('General Information')
            ]
        );
    }

    private function addHidePriceModeCatAttribute(): void
    {
        $this->eavSetup->addAttribute(
            Category::ENTITY,
            self::HIDE_PRICE_MODE_CAT_ATTR,
            [
                'type' => 'int',
                'label' => __('Display Price Mode'),
                'input' => 'select',
                'required' => false,
                'sort_order' => 4,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'wysiwyg_enabled' => false,
                'is_html_allowed_on_front' => false,
                'group' => __('General Information')
            ]
        );
    }

    private function addHidePriceModeAttr(): void
    {
        $this->eavSetup->addAttribute(
            Product::ENTITY,
            self::HIDE_PRICE_MODE_ATTR,
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => __('Display Price Mode'),
                'input' => 'select',
                'class' => '',
                'source' => Boolean::class,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => false,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'used_in_product_listing' => true,
                'apply_to' => '',
                'is_configurable' => false
            ]
        );
    }

    private function addHidePriceCustomerGroupAttr(): void
    {
        $this->eavSetup->addAttribute(
            Product::ENTITY,
            self::HIDE_PRICE_CUSTOMER_GROUP_ATTR,
            [
                'type' => 'text',
                'backend' => ArrayBackend::class,
                'frontend' => '',
                'label' => __('Use Current Price Mode By Customer Group'),
                'input' => 'multiselect',
                'class' => '',
                'source' => Group::class,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => false,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'used_in_product_listing' => true,
                'apply_to' => '',
                'is_configurable' => false
            ]
        );
    }

    public function getAliases(): array
    {
        return [];
    }

    public static function getDependencies(): array
    {
        return [];
    }
}
