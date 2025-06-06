<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Observer\Frontend;

use Amasty\RequestQuote\Model\Quote\Frontend\GetAmastyQuote;
use Amasty\RequestQuote\Model\QuoteRepository;
use Amasty\RequestQuote\Model\Source\Status;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CompleteQuote implements ObserverInterface
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var GetAmastyQuote
     */
    private $getAmastyQuote;

    public function __construct(
        CheckoutSession $checkoutSession,
        QuoteRepository $quoteRepository,
        GetAmastyQuote $getAmastyQuote
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->getAmastyQuote = $getAmastyQuote;
    }

    /**
     * @return void
     */
    public function execute(Observer $observer)
    {
        $currentQuote = $this->checkoutSession->getQuote();

        if ($amastyQuote = $this->getAmastyQuote->execute($currentQuote)) {
            $amastyQuote->setStatus(Status::COMPLETE);
            $amastyQuote->setReservedOrderId($currentQuote->getReservedOrderId());
            $this->quoteRepository->save($amastyQuote);
        }
    }
}
