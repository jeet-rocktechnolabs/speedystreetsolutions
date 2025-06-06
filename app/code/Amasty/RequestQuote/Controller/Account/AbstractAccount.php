<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Controller\Account;

use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Amasty\RequestQuote\Helper\Data;
use Amasty\RequestQuote\Model\Email\Sender;
use Amasty\RequestQuote\Model\Quote;
use Amasty\RequestQuote\Model\QuoteFactory;
use Amasty\RequestQuote\Model\Registry;
use Amasty\RequestQuote\Model\RegistryConstants;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;

abstract class AbstractAccount extends \Magento\Framework\App\Action\Action
{
    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var null|Quote
     */
    private $quote = null;

    /**
     * @var Sender
     */
    private $emailSender;

    /**
     * @var SessionFactory
     */
    private $customerSessionFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var QuoteRepositoryInterface
     */
    private $quoteRepository;

    public function __construct(
        QuoteRepositoryInterface $quoteRepository,
        Registry $registry,
        Sender $emailSender,
        SessionFactory $customerSessionFactory,
        Data $helper,
        Context $context
    ) {
        parent::__construct($context);
        $this->registry = $registry;
        $this->emailSender = $emailSender;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->helper = $helper;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @return bool|Quote
     */
    protected function loadQuote()
    {
        $result = false;

        if ($quoteId = (int)$this->_request->getParam('quote_id')) {
            $quote = $this->quoteRepository->get($quoteId, ['*']);
            if ($quote->getId()) {
                $this->quote = $quote;
                $this->registry->unregister(RegistryConstants::AMASTY_QUOTE);
                $this->registry->register(RegistryConstants::AMASTY_QUOTE, $quote);
                $result = $quote;
            }
        }

        return $result;
    }

    /**
     * @return Quote|bool|null
     */
    protected function getQuote()
    {
        if ($this->quote === null) {
            try {
                $this->quote = $this->loadQuote();
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('Request Quote does not exist'));
                $this->getActionFlag()->set('', 'no-dispatch', true);
                $this->_redirect($this->_url->getUrl('*/*/index'));
            }
        }

        return $this->quote;
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    protected function getCustomerSession()
    {
        return $this->customerSessionFactory->create();
    }

    /**
     * @return Sender
     */
    protected function getEmailSender()
    {
        return $this->emailSender;
    }

    /**
     * @inheritdoc
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->helper->isActive() ||
            !($auth = $this->getCustomerSession()->authenticate()) ||
            ($this->getQuote() &&
                $this->getCustomerSession()->getCustomerId() != $this->getQuote()->getCustomerId())
        ) {
            $this->getActionFlag()->set('', 'no-dispatch', true);
            if ($auth) {
                $this->_redirect($this->_redirect->getRefererUrl());
            }
        }

        return parent::dispatch($request);
    }
}
