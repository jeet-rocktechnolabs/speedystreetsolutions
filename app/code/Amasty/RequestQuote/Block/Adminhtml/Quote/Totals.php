<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Adminhtml\Quote;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address;

class Totals extends \Magento\Sales\Block\Adminhtml\Totals
{
    /**
     * @var \Amasty\RequestQuote\Model\Quote\Backend\Session
     */
    private $quoteSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \Amasty\RequestQuote\Model\Quote\Backend\Session $quoteSession,
        array $data = []
    ) {
        parent::__construct($context, $registry, $adminHelper, $data);
        $this->quoteSession = $quoteSession;
    }

    /**
     * Magento child blocks call getOrder for retrieve store from order. Replace order with quote.
     * Store can be retrieved from quote.
     *
     * @return QuoteInterface
     */
    public function getOrder()
    {
        return $this->getQuote();
    }

    /**
     * @return Address
     */
    public function getSource()
    {
        $this->getQuote()->collectTotals();
        if ($this->getQuote()->isVirtual()) {
            $source = $this->getQuote()->getBillingAddress();
        } else {
            $source = $this->getQuote()->getShippingAddress();
        }

        return $source;
    }

    /**
     * @return QuoteInterface
     */
    public function getQuote()
    {
        return $this->quoteSession->getQuote();
    }

    /**
     * @param DataObject $total
     * @return string
     */
    public function formatValue($total)
    {
        if (!$total->getIsFormated()) {
            return $this->displayPrices($this->getQuote(), $total->getBaseValue(), $total->getValue());
        }
        return $total->getValue();
    }

    /**
     * @param   DataObject $dataObject
     * @param   float $basePrice
     * @param   float $price
     * @param   bool $strong
     * @param   string $separator
     * @return  string
     */
    public function displayPrices($quote, $basePrice, $price, $strong = false, $separator = '<br/>')
    {
        if ($quote && $quote->isCurrencyDifferent()) {
            $res = '<strong>';
            $res .= $quote->formatBasePrice($basePrice);
            $res .= '</strong>' . $separator;
            $res .= '[' . $quote->formatPrice($price) . ']';
        } elseif ($quote) {
            $res = $quote->formatPrice($price);
            if ($strong) {
                $res = '<strong>' . $res . '</strong>';
            }
        } else {
            $res = $quote->formatPrice($price);
            if ($strong) {
                $res = '<strong>' . $res . '</strong>';
            }
        }
        return $res;
    }

    /**
     * @return $this
     */
    protected function _initTotals()
    {
        $address = $this->getSource();

        $this->_totals = [];
        $this->_totals['subtotal'] = new DataObject(
            [
                'code' => 'subtotal',
                'value' => $address->getSubtotal(),
                'base_value' => $address->getBaseSubtotal(),
                'label' => __('Subtotal'),
            ]
        );

        $this->_totals['tax'] = new DataObject(
            [
                'code' => 'tax',
                'value' => $address->getTaxAmount(),
                'base_value' => $address->getBaseTaxAmount(),
                'label' => __('Tax'),
            ]
        );

        if ($this->getQuote()->getData(QuoteInterface::SHIPPING_CONFIGURE)) {
            $this->_totals['shipping'] = new DataObject(
                [
                    'code' => 'shipping',
                    'value' => $address->getShippingAmount(),
                    'base_value' => $address->getBaseShippingAmount(),
                    'label' => __('Shipping'),
                ]
            );
        }

        $this->_totals['grand_total'] = new DataObject(
            [
                'code' => 'grand_total',
                'strong' => true,
                'value' => $address->getGrandTotal(),
                'base_value' => $address->getBaseGrandTotal(),
                'label' => __('Grand Total'),
                'area' => 'footer',
            ]
        );
        return $this;
    }

    public function addTotal(DataObject $total, $after = null)
    {
        // tax total must be added without template from this class.
        if ($total->getCode() !== 'tax') {
            parent::addTotal($total, $after);
        }

        return $this;
    }
}
