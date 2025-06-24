<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Api;

use Amasty\RequestQuote\Api\Data\QuoteItemInterface;

interface GuestQuoteItemManagementInterface
{
    /**
     * Add/update the specified quote item.
     *
     * @param string $quoteMaskId
     * @param \Amasty\RequestQuote\Api\Data\QuoteItemInterface $quoteItem
     * @return \Amasty\RequestQuote\Api\Data\QuoteItemInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function save(string $quoteMaskId, QuoteItemInterface $quoteItem): QuoteItemInterface;
}
