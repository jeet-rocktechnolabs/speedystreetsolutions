<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Config\Backend\Quote;

use Amasty\RequestQuote\Controller\Router;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class UrlKey extends Value
{
    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @var \Magento\Framework\App\Route\ConfigInterface
     */
    private $routeConfig;

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        UrlFinderInterface $urlFinder,
        \Magento\Framework\App\Route\ConfigInterface $routeConfig,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->urlFinder = $urlFinder;
        $this->routeConfig = $routeConfig;
    }

    public function validateBeforeSave()
    {
        parent::validateBeforeSave();
        if ($this->isValueChanged() && $this->getValue() !== Router::STANDARD_MODULE_ROUTE_NAME) {
            $this->validateUrlRewrite();
            $this->validateRouteName();
        }

        return $this;
    }

    /**
     * Check is URL key already in use by URL rewrite functionality.
     *
     * @throws UrlAlreadyExistsException
     */
    private function validateUrlRewrite(): void
    {
        if ($this->urlFinder->findOneByData([UrlRewrite::REQUEST_PATH => $this->getValue()])) {
            throw new UrlAlreadyExistsException(
                __('The value specified in the URL Key field would generate a URL that already exists.')
            );
        }
    }

    /**
     * Is URL key already in use by some module.
     *
     * @throws UrlAlreadyExistsException
     */
    private function validateRouteName(): void
    {
        if ($this->routeConfig->getRouteByFrontName($this->getValue(), 'frontend')) {
            throw new UrlAlreadyExistsException(
                __('The value specified in the URL Key field would generate a URL that already exists.')
            );
        }
    }
}
