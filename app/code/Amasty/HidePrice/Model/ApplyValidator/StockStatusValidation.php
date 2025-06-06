<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Model\ApplyValidator;

use Amasty\HidePrice\Helper\Data;
use Magento\Catalog\Api\Data\ProductInterface;

class StockStatusValidation implements HidePriceValidationInterface
{
    /**
     * @var Data
     */
    private $helper;

    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    public function isHidePrice(ProductInterface $product): ?bool
    {
        $checkResult = $this->helper->checkStockStatus(false, $product);
        if ($checkResult) {
            return $checkResult;
        }

        return null;
    }
}
