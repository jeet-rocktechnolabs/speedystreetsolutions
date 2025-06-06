<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Exception;

use Magento\Framework\Exception\StateException;

class NotApprovedQuoteException extends StateException
{
    public function __construct(int $quoteId)
    {
        parent::__construct(__('Quote with ID %1 not approved', $quoteId));
    }
}
