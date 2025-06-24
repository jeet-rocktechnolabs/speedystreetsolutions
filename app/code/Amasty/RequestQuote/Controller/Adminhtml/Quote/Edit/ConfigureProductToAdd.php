<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Controller\Adminhtml\Quote\Edit;

class ConfigureProductToAdd extends \Amasty\RequestQuote\Controller\Adminhtml\Quote\ActionAbstract
{
    /**
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $productId = (int)$this->getRequest()->getParam('id');
        $configureResult = new \Magento\Framework\DataObject();
        $configureResult->setOk(true);
        $configureResult->setProductId($productId);
        $configureResult->setCurrentStoreId($this->getSession()->getQuote(true)->getStore()->getId());
        $configureResult->setCurrentCustomerId($this->getSession()->getQuote(true)->getCustomerId());

        /** @var \Magento\Catalog\Helper\Product\Composite $helper */
        $helper = $this->_objectManager->get(\Magento\Catalog\Helper\Product\Composite::class);
        return $helper->renderConfigureResult($configureResult)
            ->addHandle('amasty_quote_quote_create_composite_configure');
    }
}
