<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\GoogleShopping\Setup\Patch\Data;

use Magento\Catalog\Model\Category;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Validator\ValidateException;
use Psr\Log\LoggerInterface;

/**
 * Setup data patch class for adding Category Attributes
 */
class CategoryAttributes implements DataPatchInterface
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
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        try {
            $this->addCategoryAttributes();
        } catch (\Exception $exception) {
            $this->logger->warning('CategoryAttributes: ' . $exception->getMessage());
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Add 'googleshopping_cat' & 'googleshopping_cat_exclude' category attribute
     *
     * @throws LocalizedException|ValidateException
     */
    public function addCategoryAttributes()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        if (!$eavSetup->getAttributeId(Category::ENTITY, 'googleshopping_cat')) {
            $eavSetup->addAttribute(
                Category::ENTITY,
                'googleshopping_cat',
                [
                    'type' => 'varchar',
                    'label' => 'Google Shopping Category',
                    'input' => 'text',
                    'group' => 'General Information',
                    'global' => 1,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'sort_order' => 100,
                    'default' => null
                ]
            );
        }

        if (!$eavSetup->getAttributeId(Category::ENTITY, 'googleshopping_cat_exlude')) {
            $eavSetup->addAttribute(
                Category::ENTITY,
                'googleshopping_cat_exlude',
                [
                    'type' => 'int',
                    'label' => 'Disable Category from Product-Type',
                    'input' => 'select',
                    'source' => Boolean::class,
                    'global' => 1,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'sort_order' => 100,
                    'default' => 0
                ]
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

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
