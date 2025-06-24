<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Account\Quote;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Model\ResourceModel\Quote;
use Amasty\RequestQuote\Model\UrlResolver;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\View\Element\Template;
use Amasty\RequestQuote\Model\Source\Status;

class History extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_RequestQuote::account/quote/history.phtml';

    /**
     * @var Quote\Account\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Quote\Collection|null
     */
    private $quotes = null;

    /**
     * @var SessionFactory
     */
    private $sessionFactory;

    /**
     * @var Status
     */
    private $statusConfig;

    /**
     * @var \Amasty\RequestQuote\Helper\Data
     */
    private $configHelper;

    /**
     * @var PostHelper
     */
    private $postHelper;

    /**
     * @var UrlResolver
     */
    private $urlResolver;

    public function __construct(
        Quote\Account\CollectionFactory $collectionFactory,
        Status $statusConfig,
        \Amasty\RequestQuote\Helper\Data $configHelper,
        SessionFactory $sessionFactory,
        Template\Context $context,
        PostHelper $postHelper,
        UrlResolver $urlResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->collectionFactory = $collectionFactory;
        $this->sessionFactory = $sessionFactory;
        $this->statusConfig = $statusConfig;
        $this->configHelper = $configHelper;
        $this->postHelper = $postHelper;
        $this->urlResolver = $urlResolver;
    }

    /**
     * @return Quote\Collection|null
     */
    public function getQuotes()
    {
        if ($this->quotes === null
            && ($customerId = $this->getCustomerSession()->getCustomerId())
        ) {
            $this->quotes = $this->collectionFactory->create($customerId)
                ->addFieldToFilter('amasty_quote.status', [
                    'in' => $this->statusConfig->getVisibleOnFrontStatuses()
                ])
                ->setOrder('created_at', 'desc');
        }

        return $this->quotes;
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    private function getCustomerSession()
    {
        return $this->sessionFactory->create();
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getQuotes()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'amasty.quote.history.pager'
            )->setCollection(
                $this->getQuotes()
            );
            $this->setChild('pager', $pager);
            $this->getQuotes()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @param QuoteInterface $quote
     *
     * @return string
     */
    public function getViewUrl($quote)
    {
        return $this->urlResolver->getViewUrl((int) $quote->getId());
    }

    /**
     * @param QuoteInterface $quote
     *
     * @return string
     */
    public function getDeleteUrl($quote)
    {
        return $this->getUrl('amasty_quote/account/delete', ['quote_id' => $quote->getId()]);
    }

    /**
     * @param QuoteInterface $quote
     *
     * @return string
     */
    public function getMoveUrl($quote)
    {
        return $this->_urlBuilder->getUrl('amasty_quote/move/inCart', ['quote_id' => $quote->getId()]);
    }

    /**
     * @param QuoteInterface $quote
     *
     * @return bool
     */
    public function isMoveShowed($quote)
    {
        return $quote->getStatus() == Status::APPROVED;
    }

    /**
     * @return bool
     */
    public function isExpiryColumnShow()
    {
        return $this->configHelper->getExpirationTime() !== null;
    }

    public function getExpiredDate(QuoteInterface $quote): string
    {
        $result = __('N/A')->render();
        if ($quote->getExpiredDate()
            && in_array($quote->getStatus(), [Status::APPROVED, Status::EXPIRED])
        ) {
            $result = (string) $this->formatDate($quote->getExpiredDate());
        }

        return $result;
    }

    public function getPostData(string $url): string
    {
        return $this->postHelper->getPostData($url);
    }
}
