<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Controller\Adminhtml\Quote;

use Magento\Backend\App\Action;
use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;

abstract class ActionAbstract extends \Amasty\RequestQuote\Controller\Adminhtml\Quote
{
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Amasty\RequestQuote\Model\Quote\Backend\Session
     */
    protected $quoteSession;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Amasty\RequestQuote\Model\Quote\Backend\Edit
     */
    protected $quoteEditModel;

    /**
     * @var \Amasty\RequestQuote\Model\Email\Sender
     */
    protected $emailSender;

    /**
     * @var \Amasty\RequestQuote\Helper\Data
     */
    protected $configHelper;

    /**
     * @var \Amasty\RequestQuote\Helper\Date
     */
    protected $dateHelper;

    /**
     * @var \Amasty\RequestQuote\Model\Quote\Backend\FormDataProcessor
     */
    private $formDataProcessor;

    public function __construct(
        Action\Context $context,
        \Amasty\RequestQuote\Model\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        QuoteRepositoryInterface $quoteRepository,
        \Amasty\RequestQuote\Model\Quote\Backend\Session $quoteSession,
        LoggerInterface $logger,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Framework\Escaper $escaper,
        ForwardFactory $resultForwardFactory,
        \Amasty\RequestQuote\Model\Quote\Backend\Edit $editModel,
        \Amasty\RequestQuote\Model\Email\Sender $emailSender,
        \Amasty\RequestQuote\Helper\Data $configHelper,
        \Amasty\RequestQuote\Helper\Date $dateHelper,
        \Amasty\RequestQuote\Model\Quote\Backend\FormDataProcessor $formDataProcessor
    ) {
        parent::__construct(
            $context,
            $coreRegistry,
            $fileFactory,
            $translateInline,
            $resultPageFactory,
            $resultJsonFactory,
            $resultLayoutFactory,
            $resultRawFactory,
            $quoteRepository,
            $quoteSession,
            $logger
        );
        $productHelper->setSkipSaleableCheck(true);
        $this->escaper = $escaper;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->quoteSession = $quoteSession;
        $this->backendSession = $context->getSession();
        $this->quoteEditModel = $editModel;
        $this->emailSender = $emailSender;
        $this->configHelper = $configHelper;
        $this->dateHelper = $dateHelper;
        $this->formDataProcessor = $formDataProcessor;
    }

    /**
     * @return \Amasty\RequestQuote\Model\Quote\Backend\Session
     */
    public function getSession()
    {
        return $this->quoteSession;
    }

    /**
     * @return \Amasty\RequestQuote\Api\Data\QuoteInterface
     */
    public function getQuote()
    {
        return $this->getSession()->getQuote();
    }

    /**
     * @return \Amasty\RequestQuote\Model\Quote\Backend\Edit
     */
    public function getQuoteEditModel()
    {
        return $this->quoteEditModel;
    }

    /**
     * @return $this
     */
    protected function initSession()
    {
        if ($customerId = $this->getRequest()->getParam('customer_id')) {
            $this->getSession()->setCustomerId((int)$customerId);
        }

        if ($storeId = $this->getRequest()->getParam('store_id')) {
            $this->getSession()->setStoreId((int)$storeId);
        }

        if ($currencyId = $this->getRequest()->getParam('currency_id')) {
            $this->getSession()->setCurrencyId((string)$currencyId);
            $this->getQuoteEditModel()->setRecollect(true);
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function processData()
    {
        return $this->processActionData();
    }

    /**
     * @param string $action
     * @return $this
     */
    protected function processActionData($action = null)
    {
        $isSaveAction = $action === 'save';
        $this->formDataProcessor->resolvePostData();
        $this->formDataProcessor->resolveShippingAsBilling();
        $this->formDataProcessor->resolveItems($isSaveAction);
        $this->formDataProcessor->resolveRecollect($isSaveAction);

        $this->getQuoteEditModel()->saveQuote();

        return $this;
    }

    /**
     * @return $this
     */
    protected function reloadQuote()
    {
        $this->getSession()->reloadQuote();
        return $this;
    }
}
