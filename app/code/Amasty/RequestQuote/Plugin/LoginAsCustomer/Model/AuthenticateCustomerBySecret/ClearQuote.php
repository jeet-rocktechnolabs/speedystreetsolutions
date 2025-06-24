<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Plugin\LoginAsCustomer\Model\AuthenticateCustomerBySecret;

use Amasty\RequestQuote\Model\Quote\Session as QuoteSession;
use Magento\LoginAsCustomer\Model\AuthenticateCustomerBySecret;

/**
 * We need to clear the customer quote in the session
 * before the admin user logs in as a customer via admin panel,
 * so the items from the previous customer quote do not get into another quote.
 *
 * @see \Amasty\RequestQuote\Model\Quote\Session::loadCustomerQuote
 */
class ClearQuote
{
    /**
     * @var QuoteSession
     */
    private $quoteSession;

    public function __construct(
        QuoteSession $quoteSession
    ) {
        $this->quoteSession = $quoteSession;
    }

    public function beforeExecute(
        AuthenticateCustomerBySecret $subject,
        string $secret
    ): void {
        $this->quoteSession->clearQuote();
    }
}
