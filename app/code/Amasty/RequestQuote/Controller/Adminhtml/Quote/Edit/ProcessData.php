<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Controller\Adminhtml\Quote\Edit;

class ProcessData extends \Amasty\RequestQuote\Controller\Adminhtml\Quote\ActionAbstract
{
    /**
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $this->initSession();
        $this->processActionData();
        return $this->resultForwardFactory->create()->forward('index');
    }
}
