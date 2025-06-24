<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Controller\Move;

use Amasty\RequestQuote\Api\QuoteRepositoryInterface as AmastyQuoteRepository;
use Amasty\RequestQuote\Model\Quote\Frontend\GetAmastyQuote;
use Amasty\RequestQuote\Model\Quote\Move\MergeQuotes;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\ResultFactory;
use Amasty\RequestQuote\Model\Source\Status;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface as MagentoQuoteRepository;

class InCart implements HttpPostActionInterface
{
    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var GetAmastyQuote
     */
    private $getAmastyQuote;

    /**
     * @var MergeQuotes
     */
    private $mergeQuotes;

    /**
     * @var AmastyQuoteRepository
     */
    private $amastyQuoteRepository;

    /**
     * @var MagentoQuoteRepository
     */
    private $magentoQuoteRepository;

    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    public function __construct(
        ResultFactory $resultFactory,
        RequestInterface $request,
        CheckoutSession $checkoutSession,
        GetAmastyQuote $getAmastyQuote,
        MergeQuotes $mergeQuotes,
        AmastyQuoteRepository $amastyQuoteRepository,
        MagentoQuoteRepository $magentoQuoteRepository,
        MessageManagerInterface $messageManager
    ) {
        $this->resultFactory = $resultFactory;
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
        $this->getAmastyQuote = $getAmastyQuote;
        $this->mergeQuotes = $mergeQuotes;
        $this->amastyQuoteRepository = $amastyQuoteRepository;
        $this->magentoQuoteRepository = $magentoQuoteRepository;
        $this->messageManager = $messageManager;
    }

    public function execute(): ResultRedirect
    {
        /** @var ResultRedirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $quoteId = (int) $this->request->getParam('quote_id');
        if (!$quoteId) {
            $this->messageManager->addErrorMessage(__('Quote id not passed.'));
            $resultRedirect->setRefererUrl();
            return $resultRedirect;
        }

        $currentQuote = $this->checkoutSession->getQuote();
        $amastyQuote = $this->getAmastyQuote->execute($currentQuote);
        if ($amastyQuote !== null) {
            $this->messageManager->addErrorMessage(
                __(
                    'It is possible to process one Quote at a time.
                        You have already added Quote in your cart. Please proceed to checkout.'
                )
            );
            $resultRedirect->setRefererUrl();
            return $resultRedirect;
        }

        $approvedQuote = $this->amastyQuoteRepository->get($quoteId);
        if ($approvedQuote->getStatus() == Status::APPROVED) {
            $this->mergeQuotes->execute($currentQuote, $approvedQuote);
            $this->magentoQuoteRepository->save($currentQuote);
        } else {
            $this->messageManager->addErrorMessage(__('Quote with ID %1 not approved', $quoteId));
            $resultRedirect->setRefererUrl();
            return $resultRedirect;
        }

        $resultRedirect->setPath($this->getRedirectPath());
        return $resultRedirect;
    }

    private function getRedirectPath(): string
    {
        return $this->request->getParam('redirect_url', 'checkout/cart');
    }
}
