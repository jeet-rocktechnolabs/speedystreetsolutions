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

class ConfigureQuoteItems extends \Amasty\RequestQuote\Controller\Adminhtml\Quote\Create
{
    /**
     * @var \Magento\Catalog\Helper\Product\Composite
     */
    private $compositeHelper;

    /**
     * @var \Magento\Quote\Model\Quote\ItemFactory
     */
    private $itemFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Item\OptionFactory
     */
    private $itemOptionFactory;

    public function __construct(
        Action\Context $context,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Framework\Escaper $escaper,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        \Amasty\RequestQuote\Model\Quote\Backend\Session $session,
        \Amasty\RequestQuote\Model\Quote\Backend\Edit $quoteEditModel,
        \Magento\Catalog\Helper\Product\Composite $compositeHelper,
        \Magento\Quote\Model\Quote\ItemFactory $itemFactory,
        \Magento\Quote\Model\Quote\Item\OptionFactory $itemOptionFactory,
        FormDataProcessor $formDataProcessor
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
        $this->itemFactory = $itemFactory;
        $this->itemOptionFactory = $itemOptionFactory;
    }

    /**
     * Ajax handler of composite product in quote items
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        // Prepare data
        $configureResult = new \Magento\Framework\DataObject();
        try {
            $quoteItemId = (int)$this->getRequest()->getParam('id');
            if (!$quoteItemId) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The quote item ID needs to be received. Set the ID and try again.')
                );
            }

            $quoteItem = $this->itemFactory->create()->load($quoteItemId);
            if (!$quoteItem->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The quote item needs to be loaded. Load the item and try again.')
                );
            }

            $configureResult->setOk(true);
            $optionCollection = $this->itemOptionFactory->create()
                ->getCollection()
                ->addItemFilter([$quoteItemId]);
            $quoteItem->setOptions($optionCollection->getOptionsByItem($quoteItem));

            $configureResult->setBuyRequest($quoteItem->getBuyRequest());
            $configureResult->setCurrentStoreId($quoteItem->getStoreId());
            $configureResult->setProductId($quoteItem->getProductId());
            $sessionQuote = $this->_getSession();
            $configureResult->setCurrentCustomerId($sessionQuote->getCustomerId());
        } catch (\Exception $e) {
            $configureResult->setError(true);
            $configureResult->setMessage($e->getMessage());
        }

        return $this->compositeHelper->renderConfigureResult($configureResult)
            ->addHandle('amasty_quote_quote_create_composite_configure');
    }
}
