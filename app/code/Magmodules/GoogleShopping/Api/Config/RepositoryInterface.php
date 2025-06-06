<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magmodules\GoogleShopping\Api\Config;

use Magento\Store\Api\Data\StoreInterface;

/**
 * Config repository interface
 * @api
 */
interface RepositoryInterface
{
    public const EXTENSION_CODE = 'Magmodules_GoogleShopping';
    public const XML_PATH_EXTENSION_VERSION = 'magmodules_googleshopping/general/version';
    public const MODULE_SUPPORT_LINK = 'https://www.magmodules.eu/help/%s';
    public const XML_PATH_EXTENSION_ENABLE = 'magmodules_googleshopping/general/enable';
    public const XML_PATH_DEBUG = 'magmodules_googleshopping/general/debug';

    /**
     * Get extension version
     *
     * @return string
     */
    public function getExtensionVersion(): string;

    /**
     * Get extension code
     *
     * @return string
     */
    public function getExtensionCode(): string;

    /**
     * Get Magento Version
     *
     * @return string
     */
    public function getMagentoVersion(): string;

    /**
     * Check if module is enabled
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isEnabled(int $storeId = null): bool;

    /**
     * Check if debug mode is enabled
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isDebugMode(int $storeId = null): bool;

    /**
     * Get current store
     *
     * @return StoreInterface
     */
    public function getStore(): StoreInterface;

    /**
     * Get Configuration data
     *
     * @param string $path
     * @param int|null $storeId
     * @param string|null $scope
     *
     * @return string
     */
    public function getStoreValue(string $path, int $storeId = null, string $scope = null): string;

    /**
     * Support link for extension.
     *
     * @return string
     */
    public function getSupportLink(): string;
}
