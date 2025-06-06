<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Controller\Adminhtml\Quote\Create;

use Amasty\RequestQuote\Model\Quote\Backend\FormDataProcessor;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;

class ConfigureProductToAdd extends \Amasty\RequestQuote\Controller\Adminhtml\Quote\Create
{
    /**
     * @var \Magento\Catalog\Helper\Product\Composite
     */
    private $compositeHelper;

    public function __construct(
        Action\Context $context,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Framework\Escaper $escaper,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        \Amasty\RequestQuote\Model\Quote\Backend\Session $session,
        \Amasty\RequestQuote\Model\Quote\Backend\Edit $quoteEditModel,
        FormDataProcessor $formDataProcessor,
        \Magento\Catalog\Helper\Product\Composite $compositeHelper
    ) {
        parent::__construct(
            $context,
            $productHelper,
            $escaper,
            $resultPageFactory,
            $resultForwardFactory,
            $session,
            $quoteEditModel,
            $formDataProcessor
        );
        $this->compositeHelper = $compositeHelper;
    }

    /**
     * Ajax handler of composite product in order
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        // Prepare data
        $productId = (int)$this->getRequest()->getParam('id');

        $configureResult = new \Magento\Framework\DataObject();
        $configureResult->setOk(true);
        $configureResult->setProductId($productId);
        $sessionQuote = $this->_getSession();
        $configureResult->setCurrentStoreId($sessionQuote->getStore()->getId());
        $configureResult->setCurrentCustomerId($sessionQuote->getCustomerId());

        return $this->compositeHelper->renderConfigureResult($configureResult)
            ->addHandle('amasty_quote_quote_create_composite_configure');
    }
}
