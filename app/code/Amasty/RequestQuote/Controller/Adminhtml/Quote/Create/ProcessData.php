<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Controller\Adminhtml\Quote\Create;

class ProcessData extends \Amasty\RequestQuote\Controller\Adminhtml\Quote\Create
{
    /**
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $this->_initSession();
        $this->_processData();
        return $this->resultForwardFactory->create()->forward('index');
    }
}
