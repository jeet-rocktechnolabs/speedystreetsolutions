<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Setup\Patch\Data;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Transfer data after changing attribute types
 */
class TransferData implements DataPatchInterface
{
    private const HIDE_PRICE_CUSTOMER_GROUP_ATTR = 'am_hide_price_customer_gr';
    private const HIDE_PRICE_CUSTOMER_GROUP_CAT_ATTR = 'am_hide_price_customer_gr_cat';

    private const PRODUCT_ENTITY_VARCHAR_TABLE = 'catalog_product_entity_varchar';
    private const PRODUCT_ENTITY_TEXT_TABLE = 'catalog_product_entity_text';
    private const CATEGORY_ENTITY_VARCHAR_TABLE = 'catalog_category_entity_varchar';
    private const CATEGORY_ENTITY_TEXT_TABLE = 'catalog_category_entity_text';

    /**
     * @var ModuleDataSetupInterface
     */
    private $schemaSetup;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    public function __construct(
        ModuleDataSetupInterface $schemaSetup,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->schemaSetup = $schemaSetup;
        $this->attributeRepository = $attributeRepository;
    }

    public static function getDependencies(): array
    {
        return [CreateAttributes::class];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): self
    {
        $this->moveProductAttributeData();
        $this->moveCategoryAttributeData();

        return $this;
    }

    private function moveProductAttributeData(): void
    {
        $attribute = $this->attributeRepository->get(Product::ENTITY, self::HIDE_PRICE_CUSTOMER_GROUP_ATTR);
        $this->transferData(
            self::PRODUCT_ENTITY_VARCHAR_TABLE,
            self::PRODUCT_ENTITY_TEXT_TABLE,
            (int)$attribute->getAttributeId()
        );
    }

    private function moveCategoryAttributeData(): void
    {
        $attribute = $this->attributeRepository->get(Category::ENTITY, self::HIDE_PRICE_CUSTOMER_GROUP_CAT_ATTR);
        $this->transferData(
            self::CATEGORY_ENTITY_VARCHAR_TABLE,
            self::CATEGORY_ENTITY_TEXT_TABLE,
            (int)$attribute->getAttributeId()
        );
    }

    private function transferData(string $fromTableName, string $toTableName, int $attributeId): void
    {
        $fromTable = $this->schemaSetup->getTable($fromTableName);
        $toTable = $this->schemaSetup->getTable($toTableName);
        $connection = $this->schemaSetup->getConnection();
        $select = $connection->select()
            ->from($fromTable)
            ->where('attribute_id = ?', $attributeId);
        $attributeValues = $connection->fetchAll($select);

        foreach ($attributeValues as $value) {
            unset($value['value_id']);
            $connection->insert($toTable, $value);
        }
    }
}
