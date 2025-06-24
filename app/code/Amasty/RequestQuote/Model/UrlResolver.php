<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model;

use Amasty\RequestQuote\Controller\Router;
use Magento\Framework\Url;

/**
 * Module route resolver for storefront.
 *
 * Route part of the link can be customized via system configuration of the module.
 * Company name should not be visible for customers (AJAX allowed).
 * But without company name, we can't guarantee unique url.
 * Customizable route only for visible urls, AJAX url should have static url because of sections.xml
 */
class UrlResolver
{
    /**
     * @var Url
     */
    private $frontendUrl;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Url $frontendUrl,
        ConfigProvider $configProvider
    ) {
        $this->frontendUrl = $frontendUrl;
        $this->configProvider = $configProvider;
    }

    public function getRouteKey(): string
    {
        return $this->configProvider->getRouteName() ?? Router::STANDARD_MODULE_ROUTE_NAME;
    }

    /**
     * Get URL for module action. Resolve dynamic customizable route.
     *
     * Do not process AJAX URLs throw this method. They better by static.
     *
     * @param string $path route string without module route part (the first one)
     * @param array|null $routeParams
     */
    public function resolveUrl(string $path, array $routeParams = null): string
    {
        $route = $this->getRouteKey();

        return $this->frontendUrl->getUrl($route . '/' . $path, $routeParams);
    }

    public function getAccountUrl(array $params = []): string
    {
        return $this->resolveUrl('account/index', $params);
    }

    public function getViewUrl(int $quoteId, array $params = []): string
    {
        $params['quote_id'] = $quoteId;
        return $this->resolveUrl('account/view', $params);
    }

    public function getCartUrl(array $params = []): string
    {
        return $this->resolveUrl('cart', $params);
    }

    public function getConfigureUrl(int $id, int $productId, array $params = []): string
    {
        $params['id'] = $id;
        $params['product_id'] = $productId;

        return $this->resolveUrl(
            'cart/configure',
            $params
        );
    }

    public function getSuccessUrl(array $params = []): string
    {
        return $this->resolveUrl('quote/success', $params);
    }
}
