<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Model;

use Amasty\HidePrice\Model\ApplyValidator\HidePriceValidationInterface;
use Magento\Catalog\Api\Data\ProductInterface;

class ApplyValidator
{
    /**
     * @var bool[]
     */
    private $localStorage = [];

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var HidePriceValidationInterface[]
     */
    private $validationPool;

    /**
     * @param ConfigProvider $configProvider
     * @param HidePriceValidationInterface[] $validationPool
     */
    public function __construct(ConfigProvider $configProvider, array $validationPool = [])
    {
        $this->configProvider = $configProvider;
        $this->validationPool = $validationPool;
    }

    public function isHidePrice(ProductInterface $product): bool
    {
        if (!$this->configProvider->isEnabled()) {
            return false;
        }

        $productId = $product->getId();
        if (!array_key_exists($productId, $this->localStorage)) {
            $this->localStorage[$productId] = $this->validate($product);
        }

        return $this->localStorage[$productId];
    }

    private function validate(ProductInterface $product): bool
    {
        foreach ($this->validationPool as $validation) {
            $isHidePrice = $validation->isHidePrice($product);
            if ($isHidePrice !== null) {
                return $isHidePrice;
            }
        }

        return false;
    }

    public function clearStorage(): void
    {
        $this->localStorage = [];
    }
}
