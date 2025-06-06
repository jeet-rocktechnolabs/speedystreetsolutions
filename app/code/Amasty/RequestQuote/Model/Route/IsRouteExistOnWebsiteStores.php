<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Route;

use Amasty\RequestQuote\Model\ConfigProvider;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Checking if the route exists in the website store settings.
 */
class IsRouteExistOnWebsiteStores
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        StoreManagerInterface $storeManager,
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
        $this->storeManager = $storeManager;
    }

    public function execute(string $currentRoute): bool
    {
        $routes = [];

        foreach ($this->storeManager->getWebsite()->getStores() as $store) {
            $routes[] = strtolower($this->configProvider->getRouteName((int)$store->getId()));
        }

        return in_array($currentRoute, $routes, true);
    }
}
