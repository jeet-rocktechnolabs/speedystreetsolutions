<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Adminhtml\Quote\Create\Totals;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class Subtotal extends DefaultTotals
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_RequestQuote::quote/create/totals/subtotal.phtml';

    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $_taxConfig;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Amasty\RequestQuote\Model\Quote\Backend\Session $sessionQuote,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Sales\Helper\Data $salesData,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Tax\Model\Config $taxConfig,
        array $data = []
    ) {
        $this->_taxConfig = $taxConfig;
        parent::__construct($context, $sessionQuote, $priceCurrency, $salesData, $salesConfig, $data);
    }

    /**
     * @return bool
     */
    public function displayBoth()
    {
        /**
         * without parameter store
         */
        return $this->_taxConfig->displayCartSubtotalBoth();
    }
}
