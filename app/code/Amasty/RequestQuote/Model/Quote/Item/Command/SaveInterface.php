<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote\Item\Command;

use Amasty\RequestQuote\Api\Data\QuoteItemInterface;

interface SaveInterface
{
    /**
     * Add/update the specified quote item.
     *
     * @param \Amasty\RequestQuote\Api\Data\QuoteItemInterface $quoteItem
     * @return \Amasty\RequestQuote\Api\Data\QuoteItemInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function save(QuoteItemInterface $quoteItem): QuoteItemInterface;
}
