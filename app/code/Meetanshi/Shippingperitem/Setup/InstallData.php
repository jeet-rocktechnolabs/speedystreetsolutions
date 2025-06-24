<?php
namespace Meetanshi\Shippingperitem\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\Product;

/**
 * Class InstallData
 * @package Meetanshi\Shippingperitem\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * InstallData constructor.
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            Product::ENTITY,
            'shipping_peritem',
            [
                'group' => 'General',
                'label' => 'Shipping Rate',
                'type'  => 'decimal',
                'input' => 'text',
                'source' => '',
                'source' => '',
                'default' => '0',
                'required' => false,
                'global' => Attribute::SCOPE_STORE,
                'used_in_product_listing' => true,
                'visible_on_front' => false
            ]
        );
        
        $attributeId = $eavSetup->getAttributeId(Product::ENTITY, 'shipping_peritem');
               
        foreach ($eavSetup->getAllAttributeSetIds(Product::ENTITY) as $attributeSetId) {
            $attributeGroupId = $eavSetup->getAttributeGroupId(Product::ENTITY, $attributeSetId, 'General');
            $eavSetup->addAttributeToSet(Product::ENTITY, $attributeSetId, $attributeGroupId, $attributeId);
        }
        $setup->endSetup();
    }
}
