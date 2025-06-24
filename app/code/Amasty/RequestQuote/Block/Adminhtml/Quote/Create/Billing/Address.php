<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Adminhtml\Quote\Create\Billing;

use Amasty\RequestQuote\Model\Quote\Backend\Session;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Address extends \Amasty\RequestQuote\Block\Adminhtml\Quote\Create\Form\Address
{
    /**
     * Return header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Billing Address');
    }

    /**
     * Return Header CSS Class
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'head-billing-address';
    }

    /**
     * Prepare Form and add elements to form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $this->setJsVariablePrefix('billingAddress');
        parent::_prepareForm();

        $this->_form->addFieldNameSuffix('quote[billing_address]');
        $this->_form->setHtmlNamePrefix('quote[billing_address]');
        $this->_form->setHtmlIdPrefix('quote-billing_address_');

        return $this;
    }

    /**
     * Return Form Elements values
     *
     * @return array
     */
    public function getFormValues()
    {
        return $this->getQuoteEditModel()->getBillingAddress()->getData();
    }

    /**
     * Return customer address id
     *
     * @return int|bool
     */
    public function getAddressId()
    {
        return $this->getQuoteEditModel()->getBillingAddress()->getCustomerAddressId();
    }

    /**
     * Return billing address object
     *
     * @return \Magento\Quote\Model\Quote\Address
     */
    public function getAddress()
    {
        return $this->getQuoteEditModel()->getBillingAddress();
    }

    /**
     * @return string
     */
    public function getContainerId(): string
    {
        return 'quote-billing_address_fields';
    }

    /**
     * @return string
     */
    public function getAddressContainerId(): string
    {
        return 'quote-billing_address_choice';
    }
}
