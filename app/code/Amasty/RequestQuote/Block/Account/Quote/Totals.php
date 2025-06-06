<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Account\Quote;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Model\Registry;
use Amasty\RequestQuote\Model\RegistryConstants;
use Amasty\RequestQuote\Model\Source\Status;

class Totals extends \Magento\Checkout\Block\Cart\Totals
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var array
     */
    private $allowedCodes = [
        'subtotal',
        'tax',
        'weee_tax',
        'shipping',
        'tax_shipping',
        'grand_total'
    ];

    /**
     * @var array
     */
    private $shippingCodes = [
        'shipping',
        'tax_shipping'
    ];

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Config $salesConfig,
        Registry $registry,
        array $layoutProcessors = [],
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $customerSession, $checkoutSession, $salesConfig, $layoutProcessors, $data);
    }

    protected function _construct()
    {
        if ($this->getQuote()->getStatus() == Status::COMPLETE
            || $this->getQuote()->getData(QuoteInterface::SHIPPING_CONFIGURE)
        ) {
            $this->allowedCodes = array_merge($this->allowedCodes, $this->shippingCodes);
        }
        parent::_construct();
    }

    /**
     * @return \Amasty\RequestQuote\Model\Quote
     */
    public function getQuote()
    {
        return $this->registry->registry(RegistryConstants::AMASTY_QUOTE);
    }

    /**
     * @return array
     */
    public function getTotals()
    {
        $totals = parent::getTotals();
        foreach ($totals as $code => $total) {
            if (!in_array($code, $this->allowedCodes)) {
                unset($totals[$code]);
                continue;
            }
            if ($code == 'subtotal' && isset($total['value_excl_tax'])) {
                $total['value'] = $total['value_excl_tax'];
            }
            $total['label'] = $total['title'];
        }

        return $totals;
    }

    /**
     * @param \Magento\Framework\DataObject $total
     *
     * @return string
     */
    public function formatValue($total)
    {
        if (!$total->getIsFormated()) {
            $value = $this->getQuote()->formatPrice($total->getValue());
        } else {
            $value = $total->getValue();
        }

        return $value;
    }
}
