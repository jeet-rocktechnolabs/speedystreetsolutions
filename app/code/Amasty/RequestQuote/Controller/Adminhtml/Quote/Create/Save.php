<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Controller\Adminhtml\Quote\Create;

use Amasty\RequestQuote\Model\Quote\Backend\FormDataProcessor;
use Amasty\RequestQuote\Model\Quote\Backend\SaveDataProcessor;
use Amasty\RequestQuote\Model\Quote\Backend\Session;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

class Save extends Action
{
    public const ADMIN_RESOURCE = 'Amasty_RequestQuote::create';

    /**
     * @var FormDataProcessor
     */
    private $formDataProcessor;

    /**
     * @var Session
     */
    private $quoteSession;

    /**
     * @var SaveDataProcessor
     */
    private $saveDataProcessor;

    public function __construct(
        Context $context,
        FormDataProcessor $formDataProcessor,
        Session $quoteSession,
        SaveDataProcessor $saveDataProcessor
    ) {
        parent::__construct($context);
        $this->formDataProcessor = $formDataProcessor;
        $this->quoteSession = $quoteSession;
        $this->saveDataProcessor = $saveDataProcessor;
    }

    /**
     * @return ResultInterface|void
     */
    public function execute()
    {
        $path = 'amasty_quote/*/';
        $pathParams = [];

        $model = $this->formDataProcessor->getQuoteEditModel();

        // check if the creation of a new customer is allowed
        if (!$this->_authorization->isAllowed('Magento_Customer::manage')
            && !$this->quoteSession->getCustomerId()
            && !$this->quoteSession->getQuote()->getCustomerIsGuest()
        ) {
            /** @var \Magento\Framework\Controller\Result\Forward $forward */
            $forward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);

            return $forward->forward('denied');
        }

        try {
            $quote = $model->getQuote();
            $quote->setCustomerId($this->quoteSession->getCustomerId());

            $model->setIsValidate(true);
            $this->formDataProcessor->resolvePostData();
            $this->formDataProcessor->resolveShippingAsBilling();
            $this->formDataProcessor->resolveItems(true);
            $this->formDataProcessor->resolveRecollect(true);

            $this->saveDataProcessor->postQuote();

            $this->quoteSession->clearStorage();
            $this->messageManager->addSuccessMessage(__('You created the quote.'));
            if ($this->_authorization->isAllowed('Amasty_RequestQuote::view')) {
                $pathParams = ['quote_id' => $model->getQuote()->getId()];
                $path = 'amasty_quote/quote/view';
            } else {
                $path = 'amasty_quote/quote/index';
            }
        } catch (LocalizedException $e) {
            // customer can be created before place order flow is completed and should be stored in current session
            $this->quoteSession->setCustomerId((int)$this->quoteSession->getQuote()->getCustomerId());
            $message = $e->getMessage();
            if (!empty($message)) {
                $this->messageManager->addErrorMessage($message);
            }
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Quote saving error: %1', $e->getMessage()));
        }

        return $this->resultRedirectFactory->create()->setPath($path, $pathParams);
    }
}
