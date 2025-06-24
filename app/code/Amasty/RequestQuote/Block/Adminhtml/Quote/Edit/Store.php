<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Adminhtml\Quote\Edit;

class Store extends \Amasty\RequestQuote\Block\Adminhtml\Quote\Edit\AbstractEdit
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('amasty_quote_edit_store');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Please select a store');
    }
}
