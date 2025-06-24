<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Quote;

use Amasty\RequestQuote\Model\Source\Status;
use Amasty\RequestQuote\Model\UrlResolver;
use Magento\Framework\App\Http\Context;
use Magento\Framework\View\Element\Template;
use Amasty\RequestQuote\Model\Quote\Session as QuoteSession;
use Amasty\RequestQuote\Model\Quote;

class Success extends \Magento\Framework\View\Element\Template
{
    /**
     * @var QuoteSession
     */
    private $quoteSession;

    /**
     * @var Status
     */
    private $statusConfig;

    /**
     * @var Context
     */
    private $httpContext;

    /**
     * @var UrlResolver
     */
    private $urlResolver;

    public function __construct(
        QuoteSession $quoteSession,
        Context $httpContext,
        Status $statusConfig,
        Template\Context $context,
        UrlResolver $urlResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->quoteSession = $quoteSession;
        $this->statusConfig = $statusConfig;
        $this->httpContext = $httpContext;
        $this->urlResolver = $urlResolver;
    }

    /**
     * @return void
     */
    protected function prepareBlockData()
    {
        /** @var Quote $quote */
        $quote = $this->quoteSession->getLastQuote();

        if ($quote) {
            $this->addData(
                [
                    'is_quote_visible' => $this->isVisible($quote),
                    'view_quote_url'   => $this->urlResolver->getViewUrl((int) $quote->getId()),
                    'can_view_quote'   => $this->canViewQuote($quote),
                    'quote_id'         => $quote->prepareIncrementId()
                ]
            );
        }
    }

    /**
     * @return string
     */
    public function getAdditionalInfoHtml()
    {
        return $this->_layout->renderElement('quote.success.additional.info');
    }

    /**
     * @return string
     */
    protected function _beforeToHtml()
    {
        $this->prepareBlockData();
        return parent::_beforeToHtml();
    }

    /**
     * @param Quote $quote
     * @return bool
     */
    protected function isVisible($quote)
    {
        return in_array(
            $quote->getStatus(),
            $this->statusConfig->getVisibleOnFrontStatuses()
        );
    }

    /**
     * @param Quote $quote
     * @return bool
     */
    protected function canViewQuote($quote)
    {
        return $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH)
            && $this->isVisible($quote);
    }

    /**
     * @return string
     */
    public function getContinueUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }
}
