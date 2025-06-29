<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Controller\Adminhtml\Quote\Create;

class Cancel extends \Amasty\RequestQuote\Controller\Adminhtml\Quote\Create
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($orderId = $this->_getSession()->getReordered()) {
            $this->_getSession()->clearStorage();
            $resultRedirect->setPath('amasty_quote/quote/view', ['quote_id' => $orderId]);
        } else {
            $this->_getSession()->clearStorage();
            $resultRedirect->setPath('amasty_quote/*');
        }
        return $resultRedirect;
    }
}
