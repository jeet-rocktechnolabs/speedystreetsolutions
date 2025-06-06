<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Adminhtml\Quote\Edit\Totals;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class Subtotal extends \Amasty\RequestQuote\Block\Adminhtml\Quote\Edit\Totals\DefaultTotals
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Sales::order/create/totals/subtotal.phtml';

    /**
     * @return bool
     */
    public function displayBoth()
    {
        return $this->taxConfig->displayCartSubtotalBoth();
    }
}
