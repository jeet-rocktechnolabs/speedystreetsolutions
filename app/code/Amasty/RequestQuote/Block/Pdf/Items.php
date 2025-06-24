<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Pdf;

class Items extends \Magento\Framework\View\Element\Template
{
    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function toHtml()
    {
        $quoteItems = $this->getLayout()->getBlock('quote_items');

        return $quoteItems->toHtml();
    }
}
