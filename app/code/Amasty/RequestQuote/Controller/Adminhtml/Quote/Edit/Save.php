<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Controller\Adminhtml\Quote\Edit;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Save extends \Magento\Backend\App\Action
{
    public const ADMIN_RESOURCE = \Amasty\RequestQuote\Controller\Adminhtml\Quote::ADMIN_RESOURCE;

    /**
     * @var \Amasty\RequestQuote\Model\Quote\Backend\FormDataProcessor
     */
    private $formDataProcessor;

    /**
     * @var \Amasty\RequestQuote\Model\Quote\Backend\Session
     */
    private $quoteSession;

    /**
     * @var \Amasty\RequestQuote\Model\Quote\Backend\SaveDataProcessor
     */
    private $saveDataProcessor;

    /**
     * @var \Amasty\RequestQuote\Model\Quote\Backend\QuoteLoadResolver
     */
    private $quoteLoadResolver;

    public function __construct(
        Context $context,
        \Amasty\RequestQuote\Model\Quote\Backend\FormDataProcessor $formDataProcessor,
        \Amasty\RequestQuote\Model\Quote\Backend\Session $quoteSession,
        \Amasty\RequestQuote\Model\Quote\Backend\SaveDataProcessor $saveDataProcessor,
        \Amasty\RequestQuote\Model\Quote\Backend\QuoteLoadResolver $quoteLoadResolver
    ) {
        parent::__construct($context);
        $this->formDataProcessor = $formDataProcessor;
        $this->quoteSession = $quoteSession;
        $this->saveDataProcessor = $saveDataProcessor;
        $this->quoteLoadResolver = $quoteLoadResolver;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|void
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $model = $this->formDataProcessor->getQuoteEditModel();

        try {
            // check if the creation of a new customer is allowed
            if (!$this->quoteSession->getCustomerId()
                && !$model->getQuote()->getCustomerIsGuest()
            ) {
                /** @var \Magento\Framework\Controller\Result\Forward $forward */
                $forward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);

                return $forward->forward('denied');
            }
            if ($this->quoteLoadResolver->initQuote(true)) {
                $model->setIsValidate(true);
                $this->formDataProcessor->resolvePostData();
                $this->formDataProcessor->resolveShippingAsBilling();
                $this->formDataProcessor->resolveItems(true);
                $this->formDataProcessor->resolveRecollect(true);

                $quote = $this->saveDataProcessor->postQuote(false);
                $this->quoteSession->clearStorage();
                $this->messageManager->addSuccessMessage(__('You updated the quote.'));
                $resultRedirect->setPath('amasty_quote/quote/view', ['quote_id' => $quote->getId()]);
            } else {
                $resultRedirect->setPath(
                    'amasty_quote/quote/view',
                    ['quote_id' => $this->getRequest()->getParam('quote_id')]
                );
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->quoteSession->setCustomerId($model->getQuote()->getCustomerId());
            $message = $e->getMessage();
            if (!empty($message)) {
                $this->messageManager->addErrorMessage($message);
            }
            $resultRedirect->setPath(
                'amasty_quote/quote/edit',
                ['quote_id' => $this->getRequest()->getParam('quote_id')]
            );
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Quote saving error: %1', $e->getMessage()));
            $resultRedirect->setPath('amasty_quote/*/');
        }
        return $resultRedirect;
    }
}
