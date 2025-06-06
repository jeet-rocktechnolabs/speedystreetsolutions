<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote\CustomerAccount;

use Amasty\RequestQuote\Api\Data\CustomerAccount\QuoteSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

class QuoteSearchResults extends SearchResults implements QuoteSearchResultsInterface
{
}
