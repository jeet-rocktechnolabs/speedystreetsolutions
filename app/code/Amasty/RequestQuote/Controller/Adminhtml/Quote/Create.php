<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Controller\Adminhtml\Quote;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Model\Quote\Backend\Edit as EditModel;
use Amasty\RequestQuote\Model\Quote\Backend\FormDataProcessor;
use Amasty\RequestQuote\Model\Quote\Backend\Session;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Catalog\Helper\Product;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;

abstract class Create extends Action
{
    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var EditModel
     */
    private $quoteEditModel;

    /**
     * @var FormDataProcessor
     */
    private $formDataProcessor;

    public function __construct(
        Action\Context $context,
        Product $productHelper,
        Escaper $escaper,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        Session $session,
        EditModel $quoteEditModel,
        FormDataProcessor $formDataProcessor
    ) {
        parent::__construct($context);
        $productHelper->setSkipSaleableCheck(true);
        $this->escaper = $escaper;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->session = $session;
        $this->quoteEditModel = $quoteEditModel;
        $this->formDataProcessor = $formDataProcessor;
    }

    /**
     * @return $this
     */
    protected function _initSession()
    {
        if ($customerId = $this->getRequest()->getParam('customer_id')) {
            $this->_getSession()->setCustomerId((int) $customerId);
        }

        if ($storeId = $this->getRequest()->getParam('store_id')) {
            $this->_getSession()->setStoreId((int) $storeId);
        }

        if ($currencyId = $this->getRequest()->getParam('currency_id')) {
            $this->_getSession()->setCurrencyId((string) $currencyId);
            $this->getQuoteEditModel()->setRecollect(true);
        }

        return $this;
    }

    /**
     * @return Session
     */
    protected function _getSession()
    {
        return $this->session;
    }

    /**
     * @return EditModel
     */
    protected function getQuoteEditModel()
    {
        return $this->quoteEditModel;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed($this->getAclResource());
    }

    /**
     * @return string
     */
    protected function getAclResource()
    {
        $action = strtolower($this->getRequest()->getActionName());

        switch ($action) {
            case 'index':
            case 'save':
                $aclResource = 'Amasty_RequestQuote::create';
                break;
            case 'cancel':
            case 'close':
                $aclResource = 'Amasty_RequestQuote::close';
                break;
            default:
                $aclResource = 'Amasty_RequestQuote::actions';
                break;
        }

        return $aclResource;
    }

    /**
     * @return Create
     * @throws LocalizedException
     */
    protected function _processData()
    {
        return $this->_processActionData();
    }

    /**
     * @param null $action
     * @return $this
     * @throws LocalizedException
     */
    protected function _processActionData($action = null)
    {
        $model = $this->getQuoteEditModel();

        $this->formDataProcessor->resolvePostData();
        $this->resolveCurrency();
        $this->formDataProcessor->resolveShippingAsBilling();
        $this->formDataProcessor->resolveItems();
        $this->formDataProcessor->resolveRecollect();

        $model->saveQuote();

        return $this;
    }

    /**
     * @return QuoteInterface
     */
    protected function getQuote()
    {
        return $this->_getSession()->getQuote();
    }

    private function resolveCurrency(): void
    {
        $currencyId = $this->_getSession()->getStore()->getCurrentCurrency()->getCode();
        $this->getQuoteEditModel()->getQuote()->setQuoteCurrencyCode((string) $currencyId);
    }

    /**
     * @return $this
     */
    protected function _reloadQuote()
    {
        $id = $this->getQuote()->getId();
        $this->getQuote()->load($id);

        return $this;
    }
}
