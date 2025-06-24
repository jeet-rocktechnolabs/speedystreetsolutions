<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Controller;

use Amasty\RequestQuote\Model\ConfigProvider;
use Amasty\RequestQuote\Model\Route\IsRouteExistOnWebsiteStores;
use Magento\Framework\App\Action\Redirect;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Custom router name matcher.
 *
 * Map Route name configured by system configuration with module.
 */
class Router implements RouterInterface
{
    public const MODULE_NAME = 'amasty_quote';
    public const STANDARD_MODULE_ROUTE_NAME = 'amasty_quote';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var IsRouteExistOnWebsiteStores
     */
    private $isRouteExistOnWebsiteStores;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var ActionFactory
     */
    private $actionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        ConfigProvider $configProvider,
        IsRouteExistOnWebsiteStores $isRouteExistOnWebsiteStores,
        ResponseInterface $response,
        ActionFactory $actionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->configProvider = $configProvider;
        $this->isRouteExistOnWebsiteStores = $isRouteExistOnWebsiteStores;
        $this->response = $response;
        $this->actionFactory = $actionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Match application action by request.
     *
     * Sets only designated module, controller and action is resolved by standard route matcher.
     * Sets redirect for the case when the store changes and the route remains the old one.
     *
     * @return ActionInterface|null
     */
    public function match(RequestInterface $request)
    {
        if (!$request->getModuleName()
            && $this->configProvider->isEnabled()
        ) {
            $route = $this->configProvider->getRouteName($this->storeManager->getStore()->getId());
            $frontName = $request->getFrontName();

            if ($route === null || $frontName === null) {
                return null;
            }

            if (strtolower($frontName) === strtolower($route)) {
                $request->setModuleName(self::MODULE_NAME);

                return null;
            }

            if ($this->isRouteExistOnWebsiteStores->execute(strtolower($frontName))) {
                return $this->createRedirect($frontName, $route, $request);
            }
        }

        return null;
    }

    private function createRedirect(string $frontName, string $route, RequestInterface $request): ActionInterface
    {
        $path = $request->getRequestUri();
        $path = str_replace($frontName, $route, $path);
        $this->response->setRedirect($path);
        $request->setDispatched(true);

        return $this->actionFactory->create(Redirect::class);
    }
}
