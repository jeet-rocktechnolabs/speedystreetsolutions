<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Adminhtml\Quote\Create\Store;

class Select extends \Magento\Backend\Block\Store\Switcher
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sc_store_select');
    }
}
