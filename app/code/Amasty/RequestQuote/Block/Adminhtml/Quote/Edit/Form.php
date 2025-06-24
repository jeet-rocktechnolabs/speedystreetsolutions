<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Adminhtml\Quote\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => [
                'id' => 'edit_form',
                'action' => $this->getSaveUrl(),
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            ]]
        );
        $form->setUseContainer(true);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return string
     */
    private function getSaveUrl()
    {
        return $this->getUrl('amasty_quote/quote_edit/save');
    }
}
