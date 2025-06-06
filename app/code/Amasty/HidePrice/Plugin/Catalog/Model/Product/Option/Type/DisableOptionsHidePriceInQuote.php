<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Plugin\Catalog\Model\Product\Option\Type;

use Magento\Catalog\Model\Product\Option\Type\Select;

class DisableOptionsHidePriceInQuote
{
    /**
     * @see Select::getOptionPrice()
     *
     * @param Select $subject
     * @param string $result
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeGetOptionPrice(Select $subject, string $result): void
    {
        $subject->getOption()->setData('disable_hideprice', true);
    }
}
