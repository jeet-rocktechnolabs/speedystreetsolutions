<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Adminhtml\Quote\Create;

class Customer extends \Amasty\RequestQuote\Block\Adminhtml\Quote\Create\AbstractCreate
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_customer');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Please select a customer');
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getButtonsHtml()
    {
        $result = '';
        if ($this->_authorization->isAllowed('Magento_Customer::manage')) {
            $addButtonData = [
                'label' => __('Create New Customer'),
                'onclick' => 'quote.setCustomerId(false)',
                'class' => 'primary',
            ];

            $result = $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class)
                ->setData($addButtonData)
                ->toHtml();
        }

        return $result;
    }
}
