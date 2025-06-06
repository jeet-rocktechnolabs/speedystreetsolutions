<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Controller\Move;

use Amasty\RequestQuote\Model\Quote\AdvancedMergeResult;
use Amasty\RequestQuote\Model\Quote\Frontend\GetAmastyQuote;
use Amasty\RequestQuote\Model\Quote\Session as RequestSession;
use Amasty\RequestQuote\Model\QuoteRepository as RequestRepository;
use Amasty\RequestQuote\Model\QuoteRepository;
use Amasty\RequestQuote\Model\UrlResolver;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Quote\Model\Quote as NativeQuote;
use Amasty\RequestQuote\Model\Quote;
use Amasty\RequestQuote\Model\QuoteFactory;
use Magento\Framework\App\Action\Context;

abstract class AbstractMove extends Action
{
    /**
     * @var null|Quote
     */
    private $quote = null;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    private $magentoQuoteRepository;

    /**
     * @var RequestRepository
     */
    protected $requestRepository;

    /**
     * @var UrlResolver
     */
    protected $urlResolver;

    /**
     * @var array
     */
    private $sessions = [];

    /**
     * @var GetAmastyQuote
     */
    private $getAmastyQuote;

    public function __construct(
        QuoteFactory $quoteFactory,
        QuoteRepository $quoteRepository,
        \Magento\Quote\Model\QuoteRepository $magentoQuoteRepository,
        CheckoutSession $checkoutSession,
        RequestSession $requestSession,
        RequestRepository $requestRepository,
        Context $context,
        UrlResolver $urlResolver,
        GetAmastyQuote $getAmastyQuote
    ) {
        parent::__construct($context);
        $this->sessions['checkout'] = $checkoutSession;
        $this->sessions['request'] = $requestSession;
        $this->quoteFactory = $quoteFactory;
        $this->quoteRepository = $quoteRepository;
        $this->magentoQuoteRepository = $magentoQuoteRepository;
        $this->requestRepository = $requestRepository;
        $this->urlResolver = $urlResolver;
        $this->getAmastyQuote = $getAmastyQuote;
    }

    /**
     * @return string
     */
    abstract protected function getType();

    /**
     * @return bool|Quote
     */
    private function loadQuote()
    {
        $result = false;
        if ($quoteId = (int)$this->_request->getParam('quote_id', null)) {
            $quote = $this->quoteFactory->create()->load($quoteId);
            if ($quote->getId()) {
                $result = $quote;
            }
        } else {
            $result = $this->getSession('request')->getQuote();
        }

        return $result;
    }

    /**
     * @return Quote|bool|null
     */
    protected function getQuote()
    {
        if ($this->quote === null) {
            $this->quote = $this->loadQuote();
        }

        return $this->quote;
    }

    /**
     * @param string $type
     *
     * @return RequestSession|CheckoutSession
     */
    protected function getSession($type)
    {
        return $this->sessions[$type];
    }

    /**
     * @param Quote $targetQuote
     * @param NativeQuote $originQuote
     * @param bool $targetStatus
     * @param bool $advancedMode
     */
    protected function swapQuote(
        $targetQuote,
        $originQuote,
        $targetStatus = true,
        $advancedMode = true
    ): AdvancedMergeResult {
        $result = $targetQuote->advancedMerge($originQuote, $advancedMode, $this->getType() == 'request');
        $targetQuote->setIsActive($targetStatus);
        $this->saveQuote($targetQuote);
        $this->getSession(
            $this->getType()
        )->setQuoteId(
            $targetQuote->getId()
        );
        if ($this->getType() == 'checkout') {
            $originQuote->setIsActive(false);
        }
        $this->saveQuote($originQuote);

        return $result;
    }

    /**
     * @param $quote
     * @return $this
     */
    private function saveQuote($quote)
    {
        if ($quote instanceof Quote) {
            $this->quoteRepository->save($quote);
        } else {
            $this->magentoQuoteRepository->save($quote);
        }
        return $this;
    }

    protected function getAmastyQuoteResolver(): GetAmastyQuote
    {
        return $this->getAmastyQuote;
    }
}
