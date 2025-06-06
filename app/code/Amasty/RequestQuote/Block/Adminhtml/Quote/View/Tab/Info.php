<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Adminhtml\Quote\View\Tab;

use Amasty\RequestQuote\Api\Data\QuoteInterface;

class Info extends \Amasty\RequestQuote\Block\Adminhtml\Quote\AbstractQuote implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @return \Amasty\RequestQuote\Api\Data\QuoteInterface
     */
    public function getSource()
    {
        return $this->getQuote();
    }

    /**
     * @return string
     */
    public function getItemsHtml()
    {
        return $this->getChildHtml('quote_items');
    }

    /**
     * @return string
     */
    public function getAttributesHtml(): string
    {
        return $this->getChildHtml('quote_attributes');
    }

    /**
     * @param int $orderId
     * @return string
     */
    public function getViewUrl($orderId)
    {
        return $this->getUrl('sales/*/*', ['order_id' => $orderId]);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Quote Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return float
     */
    public function getAppliedDiscount()
    {
        return (float) $this->getQuote()->getData(QuoteInterface::DISCOUNT);
    }

    /**
     * @return float
     */
    public function getAppliedSurcharge()
    {
        return (float) $this->getQuote()->getData(QuoteInterface::SURCHARGE);
    }
}
