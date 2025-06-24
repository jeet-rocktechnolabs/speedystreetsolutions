<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Email;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Amasty\RequestQuote\Model\Source\Status;

class Proposal
{
    /**
     * @var QuoteCollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var Sender
     */
    private $emailSender;

    public function __construct(
        QuoteCollectionFactory $quoteCollectionFactory,
        Sender $emailSender
    ) {
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->emailSender = $emailSender;
    }

    public function notify()
    {
        $quoteCollection = $this->quoteCollectionFactory->create()->getProposalCollection();
        /** @var \Amasty\RequestQuote\Model\Quote $quote */
        foreach ($quoteCollection as $quote) {
            if ($quote->getNeedReminderSend()) {
                $quote->setData(QuoteInterface::REMINDER_SEND, true);
                $this->emailSender->sendReminderEmail($quote);
            }
            if ($quote->getNeedExpiredSend()) {
                $quote->setStatus(Status::EXPIRED);
                $this->emailSender->sendExpiredEmail($quote);
            }
            $quote->save();
        }
    }
}
