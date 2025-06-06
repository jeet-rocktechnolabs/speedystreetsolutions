<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Adminhtml\Quote\Edit;

class History extends \Amasty\RequestQuote\Block\Adminhtml\Quote\View\History
{
    /**
     * @return \Amasty\RequestQuote\Model\Quote|\Magento\Quote\Model\Quote|mixed
     */
    public function getQuote()
    {
        return $this->getQuoteSession()->getParentQuote();
    }
}
