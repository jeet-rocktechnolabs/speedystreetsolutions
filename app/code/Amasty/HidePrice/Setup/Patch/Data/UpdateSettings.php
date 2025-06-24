<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Setup\Patch\Data;

use Amasty\HidePrice\Model\Source\ReplaceButton;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateSettings implements DataPatchInterface
{
    private const TABLE_CORE_CONFIG_DATA = 'core_config_data';

    private const REPLACE_LINK_SETTING_PATH = 'amasty_hide_price/information/replace_link';
    private const REPLACE_WITH_SETTING_PATH = 'amasty_hide_price/information/replace_with';
    private const REDIRECT_LINK_SETTING_PATH = 'amasty_hide_price/information/redirect_link';

    public const FORM_SELECTOR = 'form[data-role="tocart-form"],';
    public const NEW_FORM_SELECTOR = 'form[data-role="tocart-form"] button,';
    public const ADD_TO_CART_SETTING_PATH = 'amasty_hide_price/developer/addtocart';

    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    public function __construct(
        ModuleDataSetupInterface $setup
    ) {
        $this->setup = $setup;
    }

    public function apply(): self
    {
        $this->updateReplaceLinkSetting();
        $this->updateAddToCartSetting();

        return $this;
    }

    private function updateAddToCartSetting(): void
    {
        $connection = $this->setup->getConnection();
        $tableName = $this->setup->getTable(self::TABLE_CORE_CONFIG_DATA);

        $select = $this->setup->getConnection()->select()
            ->from($tableName, ['path', 'value', 'scope', 'scope_id', 'config_id'])
            ->where('path = ?', self::ADD_TO_CART_SETTING_PATH);

        $replaceLinks = $connection->fetchAll($select);

        foreach ($replaceLinks as $replaceLink) {
            if (isset($replaceLink['value'])) {
                if (strpos($replaceLink['value'], self::FORM_SELECTOR) !== false) {
                    $value = str_replace(self::FORM_SELECTOR, self::NEW_FORM_SELECTOR, $replaceLink['value']);
                    $connection->update(
                        $tableName,
                        ['value' => $value],
                        ['config_id = ?' => $replaceLink['config_id']]
                    );
                }
            }
        }
    }

    private function updateReplaceLinkSetting(): void
    {
        $connection = $this->setup->getConnection();
        $tableName = $this->setup->getTable(self::TABLE_CORE_CONFIG_DATA);

        $select = $this->setup->getConnection()->select()
            ->from($tableName, ['path', 'value', 'scope', 'scope_id'])
            ->where('path = ?', self::REPLACE_LINK_SETTING_PATH);

        $replaceLinks = $connection->fetchAll($select);

        foreach ($replaceLinks as $replaceLink) {
            if (isset($replaceLink['value'])) {
                if ($replaceLink['value'] === 'AmastyHidePricePopup') {
                    $updateData[] = [
                        'value' => ReplaceButton::HIDE_PRICE_POPUP,
                        'path' => self::REPLACE_WITH_SETTING_PATH,
                        'scope' => $replaceLink['scope'],
                        'scope_id' => $replaceLink['scope_id']
                    ];
                } else {
                    $updateData[] = [
                        'value' => ReplaceButton::REDIRECT_URL,
                        'path' => self::REPLACE_WITH_SETTING_PATH,
                        'scope' => $replaceLink['scope'],
                        'scope_id' => $replaceLink['scope_id']
                    ];
                    $updateData[] = [
                        'value' => $replaceLink['value'],
                        'path' => self::REDIRECT_LINK_SETTING_PATH,
                        'scope' => $replaceLink['scope'],
                        'scope_id' => $replaceLink['scope_id']
                    ];
                }
            }
        }

        if (!empty($updateData)) {
            $connection->insertOnDuplicate(
                $tableName,
                $updateData
            );
        }
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
