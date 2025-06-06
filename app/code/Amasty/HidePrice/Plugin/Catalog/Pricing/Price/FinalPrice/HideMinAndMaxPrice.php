<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Plugin\Catalog\Pricing\Price\FinalPrice;

use Amasty\HidePrice\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Amount\BaseFactory as BaseAmountFactory;
use Magento\Framework\Registry;

class HideMinAndMaxPrice
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var BaseAmountFactory
     */
    private $baseAmountFactory;

    public function __construct(
        Data $helper,
        Registry $registry,
        BaseAmountFactory $baseAmountFactory
    ) {
        $this->helper = $helper;
        $this->registry = $registry;
        $this->baseAmountFactory = $baseAmountFactory;
    }

    public function afterGetMinimalPrice(FinalPrice $subject, AmountInterface $result): AmountInterface
    {
        if ($this->isNeedHidePrice($subject->getProduct())) {
            $result = $this->getZeroPrice();
        }

        return $result;
    }

    public function afterGetMaximalPrice(FinalPrice $subject, AmountInterface $result): AmountInterface
    {
        if ($this->isNeedHidePrice($subject->getProduct())) {
            $result = $this->getZeroPrice();
        }

        return $result;
    }

    private function isNeedHidePrice(Product $product): bool
    {
        if ($this->helper->getModuleConfig('information/hide_price')
            && !$this->registry->registry('hideprice_off')
            && $this->helper->isApplied($product)
        ) {
            return true;
        }

        return false;
    }

    private function getZeroPrice(): AmountInterface
    {
        return $this->baseAmountFactory->create(['amount' => 0]);
    }
}
