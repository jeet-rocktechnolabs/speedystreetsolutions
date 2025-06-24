<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Adminhtml\Quote\Edit\Tab;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Model\Quote\Backend\Edit as QuoteEditModel;
use Magento\Framework\App\ObjectManager;

class Info extends \Amasty\RequestQuote\Block\Adminhtml\Quote\AbstractQuote implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var QuoteEditModel
     */
    private $quoteEditModel;

    /**
     * @return \Amasty\RequestQuote\Api\Data\QuoteInterface
     */
    public function getSource()
    {
        return $this->getQuote();
    }

    /**
     * @return \Magento\Quote\Model\Quote|mixed
     */
    public function getQuote()
    {
        return $this->getSession()->getParentQuote();
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
     * @param int $quoteId
     * @return string
     */
    public function getViewUrl($quoteId)
    {
        return $this->getUrl('amasty_quote/*/*', ['quote' => $quoteId]);
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
     * @return bool
     */
    public function isShippingConfigured(): bool
    {
        if ($this->getQuoteEditModel()->hasData(QuoteInterface::SHIPPING_CONFIGURE)) {
            $result = (bool) $this->getQuoteEditModel()->getData(QuoteInterface::SHIPPING_CONFIGURE);
        } elseif ($this->getQuote()->hasData(QuoteInterface::SHIPPING_CONFIGURE)) {
            $result = (bool) $this->getQuote()->getData(QuoteInterface::SHIPPING_CONFIGURE);
        } elseif ($this->getParentQuote()->hasData(QuoteInterface::SHIPPING_CONFIGURE)) {
            $result = (bool) $this->getParentQuote()->getData(QuoteInterface::SHIPPING_CONFIGURE);
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * @return QuoteEditModel
     */
    protected function getQuoteEditModel(): QuoteEditModel
    {
        if ($this->quoteEditModel === null) {
            $this->quoteEditModel = ObjectManager::getInstance()->get(QuoteEditModel::class);
        }

        return $this->quoteEditModel;
    }
}
