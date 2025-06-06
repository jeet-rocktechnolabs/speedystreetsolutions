<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Model\ApplyValidator;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * SPI
 */
interface HidePriceValidationInterface
{
    /**
     * Validate Hide Price Settings by Product
     *
     * Return true - hide price for product
     * Return false - show price for product
     * Return null - product is not configured for this validation proceed to the next validation
     *
     * @param ProductInterface $product
     * @return bool|null
     */
    public function isHidePrice(ProductInterface $product): ?bool;
}
