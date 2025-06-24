<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Controller\Adminhtml\Quote;

class Index extends \Amasty\RequestQuote\Controller\Adminhtml\Quote
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Quotes'));
        return $resultPage;
    }
}
