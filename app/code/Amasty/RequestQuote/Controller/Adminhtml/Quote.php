<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Controller\Adminhtml;

use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Amasty\RequestQuote\Model\Quote\Backend\QuoteLoadResolver;
use Amasty\RequestQuote\Model\Registry;
use Amasty\RequestQuote\Model\RegistryConstants;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;
use Psr\Log\LoggerInterface;

abstract class Quote extends \Magento\Backend\App\Action
{
    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_RequestQuote::manage_quotes';

    /**
     * @var string[]
     */
    protected $_publicActions = ['view', 'index'];

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\Framework\Translate\InlineInterface
     */
    protected $translateInline;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var QuoteRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Amasty\RequestQuote\Model\Quote\Backend\Session
     */
    protected $quoteSession;

    public function __construct(
        Action\Context $context,
        Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        QuoteRepositoryInterface $quoteRepository,
        \Amasty\RequestQuote\Model\Quote\Backend\Session $quoteSession,
        LoggerInterface $logger
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->fileFactory = $fileFactory;
        $this->translateInline = $translateInline;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->quoteRepository = $quoteRepository;
        $this->quoteSession = $quoteSession;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Amasty_RequestQuote::manage_quotes');
        $resultPage->addBreadcrumb(__('Sales'), __('Sales'));
        $resultPage->addBreadcrumb(__('Quotes'), __('Quotes'));
        return $resultPage;
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface|false
     * @deprecated moved to separated class
     */
    protected function initQuote($editMode = false)
    {
        return $this->_objectManager->get(QuoteLoadResolver::class)->initQuote($editMode);
    }

    /**
     * @param $quote
     * @return bool
     * @deprecated moved to separated class
     */
    public function validateSession($quote)
    {
        return $this->_objectManager->get(QuoteLoadResolver::class)->validateSession($quote);
    }

    /**
     * @return \Amasty\RequestQuote\Model\Quote\Backend\Session
     */
    public function getSession()
    {
        return $this->quoteSession;
    }

    /**
     * @return bool
     */
    protected function isValidPostRequest()
    {
        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        $isPost = $this->getRequest()->isPost();
        return ($formKeyIsValid && $isPost);
    }
}
