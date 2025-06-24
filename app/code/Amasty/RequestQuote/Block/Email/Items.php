<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Email;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\ViewModel\QuoteLoader;
use Magento\Sales\Block\Items\AbstractItems;

class Items extends AbstractItems
{
    public function getQuote(): ?QuoteInterface
    {
        $quoteId = $this->getData('quote_id');
        /** @var QuoteLoader $quoteLoader */
        $quoteLoader = $this->getData('quote_loader');

        if ($quoteId && $quoteLoader) {
            return $quoteLoader->load((int) $quoteId);
        }

        return null;
    }
}
