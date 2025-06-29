<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Adminhtml\Quote\Edit\Validation;

class Redirect extends \Magento\Backend\Block\Template
{
    /**
     * @return string
     */
    public function toHtml()
    {
        return $this->getData('redirect_url') ?: $this->getUrl('amasty_quote/quote/index');
    }
}
