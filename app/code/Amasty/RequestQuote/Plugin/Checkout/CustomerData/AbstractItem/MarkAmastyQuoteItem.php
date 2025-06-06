<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Plugin\Checkout\CustomerData\AbstractItem;

use Magento\Checkout\CustomerData\AbstractItem;
use Magento\Quote\Model\Quote\Item;

class MarkAmastyQuoteItem
{
    /**
     * Used for mark amasty quote items in cart section.
     */
    public const AMASTY_QUOTE_ITEM_MARK = 'is_amasty_quote_item';

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetItemData(AbstractItem $subject, array $result, Item $item): array
    {
        $result[self::AMASTY_QUOTE_ITEM_MARK] = (bool) $item->getOptionByCode('amasty_quote_price');
        return $result;
    }
}
