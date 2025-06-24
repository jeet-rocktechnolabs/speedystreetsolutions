<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Adminhtml\Quote\Edit\Totals;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Tax\Helper\Data as TaxHelper;

class DefaultTotals extends \Amasty\RequestQuote\Block\Adminhtml\Quote\Edit\Totals
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Sales::order/create/totals/default.phtml';

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Amasty\RequestQuote\Model\Quote\Backend\Session $sessionQuote,
        \Amasty\RequestQuote\Model\Quote\Backend\Edit $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Sales\Helper\Data $salesData,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Tax\Model\Config $taxConfig,
        TaxHelper $taxHelper,
        array $data = []
    ) {
        $data['taxHelper'] = $taxHelper;
        parent::__construct(
            $context,
            $sessionQuote,
            $orderCreate,
            $priceCurrency,
            $salesData,
            $salesConfig,
            $taxConfig,
            $data
        );
    }

    /**
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->getSession()->getStore();
    }
}
