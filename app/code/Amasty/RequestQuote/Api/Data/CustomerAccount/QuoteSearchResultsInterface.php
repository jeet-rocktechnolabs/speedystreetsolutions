<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Api\Data\CustomerAccount;

use Magento\Framework\Api\SearchResultsInterface;

interface QuoteSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * @return \Amasty\RequestQuote\Api\Data\CustomerAccount\QuoteInterface[]
     */
    public function getItems();

    /**
     * @param \Amasty\RequestQuote\Api\Data\CustomerAccount\QuoteInterface[] $items
     */
    public function setItems(array $items);
}
