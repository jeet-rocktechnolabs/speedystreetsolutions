<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\GoogleShopping\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Validator\ValidateException;
use Psr\Log\LoggerInterface;

/**
 * Setup data patch class for adding Product Attributes
 */
class ProductAttributes implements DataPatchInterface
{

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CategoryAttributes constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param LoggerInterface $logger
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup,
        LoggerInterface $logger
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        try {
            $this->addProductAttributes();
        } catch (\Exception $exception) {
            $this->logger->warning('ProductAttributes Patch: ' . $exception->getMessage());
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Add 'googleshopping_exclude' & 'googleshopping_category' product attribute
     *
     * @throws LocalizedException|ValidateException
     */
    public function addProductAttributes()
    {
        $groupName = 'Google Shopping';

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $attributeSetIds = $eavSetup->getAllAttributeSetIds(Product::ENTITY);

        foreach ($attributeSetIds as $attributeSetId) {
            $eavSetup->addAttributeGroup(Product::ENTITY, $attributeSetId, $groupName, 1000);
        }

        $eavSetup->addAttribute(
            Product::ENTITY,
            'googleshopping_exclude',
            [
                'group' => $groupName,
                'type' => 'int',
                'label' => 'Exclude for Google Shopping',
                'input' => 'boolean',
                'source' => Boolean::class,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'default' => '0',
                'user_defined' => true,
                'required' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => 'simple,configurable,virtual,bundle,downloadable',
                'frontend_input' => 'boolean',
            ]
        );

        $attribute = $eavSetup->getAttribute(Product::ENTITY, 'googleshopping_exclude');
        foreach ($attributeSetIds as $attributeSetId) {
            $eavSetup->addAttributeToGroup(
                Product::ENTITY,
                $attributeSetId,
                $groupName,
                $attribute['attribute_id'],
                110
            );
        }

        $eavSetup->addAttribute(
            Product::ENTITY,
            'googleshopping_category',
            [
                'group' => $groupName,
                'type' => 'varchar',
                'label' => 'Google Shopping Product Category',
                'note' => 'Overwrite the Google Shopping Category from your category configuration and default ' .
                    'configuration on product level with this open text field. You can implement the full path or ' .
                    'the ID from the Google Shopping requirements.',
                'input' => 'text',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
            ]
        );

        $attribute = $eavSetup->getAttribute(Product::ENTITY, 'googleshopping_category');
        foreach ($attributeSetIds as $attributeSetId) {
            $eavSetup->addAttributeToGroup(
                Product::ENTITY,
                $attributeSetId,
                $groupName,
                $attribute['attribute_id'],
                111
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
