<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Exception;

use Magento\Framework\Exception\StateException;

class QuoteAlreadyInCartException extends StateException
{
}
