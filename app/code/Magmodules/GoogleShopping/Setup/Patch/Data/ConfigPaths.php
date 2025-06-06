<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\GoogleShopping\Setup\Patch\Data;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Setup data patch class for adding Product Attributes
 */
class ConfigPaths implements DataPatchInterface
{

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var ValueInterface
     */
    private $configReader;
    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ValueInterface $configReader
     * @param WriterInterface $configWriter
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ValueInterface $configReader,
        WriterInterface $configWriter
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->configReader = $configReader;
        $this->configWriter = $configWriter;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->changeConfigPaths();
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Change config paths
     *
     * @throws LocalizedException
     */
    public function changeConfigPaths()
    {
        $collection = $this->configReader->getCollection()
            ->addFieldToFilter("path", "magmodules_googleshopping/advanced/parent_atts");

        foreach ($collection as $config) {
            /** @var Value $config */
            $this->configWriter->save(
                "magmodules_googleshopping/types/configurable_parent_atts",
                $config->getValue(),
                $config->getScope(),
                $config->getScopeId()
            );
            $this->configWriter->delete(
                "magmodules_googleshopping/advanced/parent_atts",
                $config->getScope(),
                $config->getScopeId()
            );
        }

        $collection = $this->configReader->getCollection()
            ->addFieldToFilter("path", "magmodules_googleshopping/advanced/relations");

        foreach ($collection as $config) {
            /** @var Value $config */
            if ($config->getValue() == 1) {
                $this->configWriter->save(
                    "magmodules_googleshopping/types/configurable",
                    'simple',
                    $config->getScope(),
                    $config->getScopeId()
                );
            }
            $this->configWriter->delete(
                "magmodules_googleshopping/advanced/relations",
                $config->getScope(),
                $config->getScopeId()
            );
        }

        $collection = $this->configReader->getCollection()
            ->addFieldToFilter("path", "magmodules_googleshopping/general/enable")
            ->addFieldToFilter("scope_id", ["neq" => 0]);

        foreach ($collection as $config) {
            /** @var Value $config */
            $this->configWriter->delete(
                "magmodules_googleshopping/general/enable",
                $config->getScope(),
                $config->getScopeId()
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
