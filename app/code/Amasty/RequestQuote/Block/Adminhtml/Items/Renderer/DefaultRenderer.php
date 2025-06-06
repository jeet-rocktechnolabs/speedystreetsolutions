<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Adminhtml\Items\Renderer;

use Magento\Quote\Model\Quote\Item\AbstractItem as QuoteItem;

class DefaultRenderer extends \Amasty\RequestQuote\Block\Adminhtml\Items\AbstractItems
{
    /**
     * @return QuoteItem
     */
    public function getItem()
    {
        return $this->_getData('item');
    }
}
