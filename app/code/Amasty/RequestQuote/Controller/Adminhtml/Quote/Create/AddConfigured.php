<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Controller\Adminhtml\Quote\Create;

class AddConfigured extends \Amasty\RequestQuote\Controller\Adminhtml\Quote\Create
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $errorMessage = null;
        try {
            $this->_initSession()->_processData();
        } catch (\Exception $e) {
            $this->_reloadQuote();
            $errorMessage = $e->getMessage();
        }

        // Form result for client javascript
        $updateResult = new \Magento\Framework\DataObject();
        if ($errorMessage) {
            $updateResult->setError(true);
            $updateResult->setMessage($errorMessage);
        } else {
            $updateResult->setOk(true);
        }

        $updateResult->setJsVarName($this->getRequest()->getParam('as_js_varname'));
        $this->_getSession()->setCompositeProductResult($updateResult);
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath('catalog/product/showUpdateResult');
    }
}
