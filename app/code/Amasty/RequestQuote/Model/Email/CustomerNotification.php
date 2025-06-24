<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Email;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Helper\Cart as CartHelper;
use Amasty\RequestQuote\Helper\Data;
use Amasty\RequestQuote\Model\Email\Sender as EmailSender;
use Amasty\RequestQuote\Model\Source\CustomerNotificationTemplates;
use Amasty\RequestQuote\Model\UrlResolver;
use IntlDateFormatter;

class CustomerNotification
{
    /**
     * @var Sender
     */
    private $emailSender;

    /**
     * @var UrlResolver
     */
    private $urlResolver;

    /**
     * @var CartHelper
     */
    private $cartHelper;

    public function __construct(EmailSender $emailSender, UrlResolver $urlResolver, CartHelper $cartHelper)
    {
        $this->emailSender = $emailSender;
        $this->urlResolver = $urlResolver;
        $this->cartHelper = $cartHelper;
    }

    public function execute(QuoteInterface $quote): void
    {
        $quote['created_date_formatted'] = $quote->getCreatedAtFormatted(\IntlDateFormatter::MEDIUM);
        $quote['submitted_date_formatted'] = $quote->getSubmitedDateFormatted(\IntlDateFormatter::MEDIUM);
        $email = $quote->getCustomerIsGuest()
            ? $quote->getCustomerEmail()
            : $quote->getCustomer()->getEmail();

        $this->emailSender->sendEmail(
            Data::CONFIG_PATH_CUSTOMER_SUBMIT_EMAIL,
            $email,
            [
                'viewUrl' => $this->urlResolver->getViewUrl((int) $quote->getQuoteId()),
                'quote' => $quote,
                'remarks' => $this->cartHelper->retrieveCustomerNote(
                    $quote->getRemarks()
                )
            ],
            CustomerNotificationTemplates::SUBMITTED_QUOTE
        );
    }
}
