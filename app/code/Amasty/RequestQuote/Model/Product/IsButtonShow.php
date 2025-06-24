<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Product;

use Amasty\RequestQuote\Helper\Data as Helper;
use Amasty\RequestQuote\Model\ConfigProvider;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * @api
 */
class IsButtonShow
{
    /**
     * @var string[]
     */
    private $allowedProductTypes = [
        'simple',
        'configurable',
        'downloadable',
        'virtual',
        'grouped',
        'bundle'
    ];

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider, array $allowedProductTypes = [])
    {
        $this->configProvider = $configProvider;
        $this->allowedProductTypes = array_merge($this->allowedProductTypes, $allowedProductTypes);
    }

    public function execute(ProductInterface $product): bool
    {
        return $this->configProvider->isActive()
            && !$product->getData(Helper::ATTRIBUTE_NAME_HIDE_BUY_BUTTON)
            && empty(array_uintersect(
                $product->getCategoryIds(),
                $this->configProvider->getExcludeCategories(),
                'strcmp'
            ))
            && in_array($product->getTypeId(), $this->allowedProductTypes);
    }
}
