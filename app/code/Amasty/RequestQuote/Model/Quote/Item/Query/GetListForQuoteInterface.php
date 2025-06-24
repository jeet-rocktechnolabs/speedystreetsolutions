<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote\Item\Query;

interface GetListForQuoteInterface
{
    /**
     * Lists items that are assigned to a specified active quote cart.
     *
     * @param int $quoteId
     * @return \Amasty\RequestQuote\Api\Data\QuoteItemInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(int $quoteId): array;
}
