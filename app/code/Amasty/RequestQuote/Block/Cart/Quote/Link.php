<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Cart\Quote;

class Link extends \Magento\Framework\View\Element\Template
{
    /**
     * @return string
     */
    public function getQuoteSubmitUrl()
    {
        return $this->getUrl('amasty_quote/cart/submit');
    }
}
