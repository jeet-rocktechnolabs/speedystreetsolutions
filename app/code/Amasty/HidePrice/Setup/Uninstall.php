<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    private const HIDE_PRICE_REQUEST_TABLE = 'amasty_hideprice_request';

    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $this->uninstallTables($setup);
        $this->uninstallConfigData($setup);
    }

    private function uninstallTables(SchemaSetupInterface $setup): void
    {
        $tablesToDrop = [
            self::HIDE_PRICE_REQUEST_TABLE
        ];

        foreach ($tablesToDrop as $table) {
            $setup->getConnection()->dropTable(
                $setup->getTable($table)
            );
        }
    }

    private function uninstallConfigData(SchemaSetupInterface $setup): void
    {
        $configTable = $setup->getTable('core_config_data');
        $setup->getConnection()->delete($configTable, "`path` LIKE 'amasty_hide_price%'");
    }
}
