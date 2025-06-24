<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\AbandonedCart\Block\Adminhtml\System\Config\Button;

class Load extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * Retrieve element HTML markup
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return mixed
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setData([
            'html_id' => $element->getHtmlId(),
        ]);
        return $this->getButtonHtml();
    }

    /**
     * Retrieve button HTML markup
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getButtonHtml()
    {
        $onClick = "javascript:abandonedCartMass('".$this->getActionUrl()."', '".$this->getStoreId()."');";
        $onClick.= 'return false;';

        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData([
            'id' => 'abandoned_cart_mass_action_load',
            'label' => $this->getFrontLabel(),
            'onclick' => $onClick
        ]);

        return $button->toHtml();
    }

    /**
     * Return store id
     * @return int
     */
    public function getStoreId()
    {
        return $this->getRequest()->getParam('store')? $this->getRequest()->getParam('store'):0;
    }

    /**
     * Returns button frontend label
     * @return \Magento\Framework\Phrase
     */
    public function getFrontLabel()
    {
        return __('Load Sample Data');
    }

    /**
     * Returns action url
     * @return string
     */
    public function getActionUrl()
    {
        return $this->getUrl('abandonedcart/manage/loaddata');
    }
}
