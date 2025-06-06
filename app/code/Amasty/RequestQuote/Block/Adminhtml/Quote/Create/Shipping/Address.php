<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Adminhtml\Quote\Create\Shipping;

class Address extends \Amasty\RequestQuote\Block\Adminhtml\Quote\Create\Form\Address
{
    /**
     * Return header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Shipping Address');
    }

    /**
     * Return Header CSS Class
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'head-shipping-address';
    }

    /**
     * Prepare Form and add elements to form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $this->setJsVariablePrefix('shippingAddress');
        parent::_prepareForm();

        $this->_form->addFieldNameSuffix('quote[shipping_address]');
        $this->_form->setHtmlNamePrefix('quote[shipping_address]');
        $this->_form->setHtmlIdPrefix('quote-shipping_address_');

        return $this;
    }

    /**
     * Return is shipping address flag
     *
     * @return true
     */
    public function getIsShipping()
    {
        return true;
    }

    /**
     * Same as billing address flag
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsAsBilling()
    {
        return $this->getQuoteEditModel()->getShippingAddress()->getSameAsBilling();
    }

    /**
     * Return Form Elements values
     *
     * @return array
     */
    public function getFormValues()
    {
        return $this->getAddress()->getData();
    }

    /**
     * Return customer address id
     *
     * @return int|bool
     */
    public function getAddressId()
    {
        return $this->getAddress()->getCustomerAddressId();
    }

    /**
     * Return address object
     *
     * @return \Magento\Quote\Model\Quote\Address
     */
    public function getAddress()
    {
        if ($this->getIsAsBilling()) {
            $address = $this->getQuoteEditModel()->getBillingAddress();
        } else {
            $address = $this->getQuoteEditModel()->getShippingAddress();
        }

        return $address;
    }

    /**
     * Return is address disabled flag
     *
     * Return true is the quote is virtual
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsDisabled()
    {
        return $this->getQuote()->isVirtual();
    }

    /**
     * @return string
     */
    public function getContainerId(): string
    {
        return 'quote-shipping_address_fields';
    }

    /**
     * @return string
     */
    public function getAddressContainerId(): string
    {
        return 'quote-shipping_address_choice';
    }
}
