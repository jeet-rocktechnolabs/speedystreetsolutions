<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model;

use Amasty\RequestQuote\Api\Data\QuoteSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

class QuoteSearchResults extends SearchResults implements QuoteSearchResultsInterface
{
}
