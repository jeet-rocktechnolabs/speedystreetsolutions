<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Controller\Adminhtml\Request;

class Index extends \Amasty\HidePrice\Controller\Adminhtml\Request
{
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Get a Quote Requests'));
        $this->_view->renderLayout();
    }
}
