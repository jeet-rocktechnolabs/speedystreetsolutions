<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Adminhtml\Quote\Create\Customer;

class Grid extends \Magento\Backend\Block\Widget\Grid
{
    /**
     * @inheritdoc
     */
    public function toHtml()
    {
        $html = parent::toHtml();
        $html = preg_replace(
            '@require\(deps@s',
            'deps.push(\'jquery\');deps.push(\'Amasty_RequestQuote/quote/create/form\');$0',
            $html,
            1
        );

        return $html;
    }
}
