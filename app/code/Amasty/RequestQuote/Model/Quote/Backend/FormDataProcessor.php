<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote\Backend;

use Magento\Catalog\Helper\Product;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;

class FormDataProcessor
{
    /**
     * @var Edit
     */
    private $quoteEditModel;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Product
     */
    private $productHelper;

    public function __construct(
        Edit $quoteEditModel,
        RequestInterface $request,
        Product $productHelper
    ) {
        $this->quoteEditModel = $quoteEditModel;
        $this->request = $request;
        $this->productHelper = $productHelper;
    }

    /**
     * @return Edit
     */
    public function getQuoteEditModel(): Edit
    {
        return $this->quoteEditModel;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * Import POST data to model
     */
    public function resolvePostData(): void
    {
        $request = $this->getRequest();
        $data = $request->getParam('quote');
        $model = $this->getQuoteEditModel();

        $model->setResetPriceModificators(
            $request->getParam('reset_price_modificators', null)
        );

        if ($data) {
            $quote = $model->getQuote();
            if ($request->getParam('shipping_same_as_billing')) {
                $data['shipping_same_as_billing'] = true;
            }

            if ($quote->isVirtual()) {
                $data['is_quote_virtual'] = true;
            }

            $model->importPostData($data);
        }
    }

    public function resolveShippingAsBilling(): void
    {
        $model = $this->getQuoteEditModel();
        $quote = $model->getQuote();

        if (!$quote->isVirtual()) {
            $request = $this->getRequest();
            $syncFlag = $request->getParam('shipping_as_billing');
            if ($syncFlag === null) {
                $shippingAddress = $quote->getShippingAddress();
                $shippingMethod = $shippingAddress->getShippingMethod();
                if (empty($shippingMethod) && ($shippingAddress->getSameAsBilling() || !$shippingAddress->getId())) {
                    $model->setShippingAsBilling(1);
                }
            } else {
                $model->setShippingAsBilling((int) $syncFlag);
            }
        }
    }

    /**
     * Add/update/remove quote items
     */
    public function resolveItems(
        bool $isSaveAction = false
    ): void {
        $model = $this->getQuoteEditModel();
        $request = $this->getRequest();
        $items = $request->getParam('item', null);

        if ($items) {
            $items = $this->processItemsFiles($items);

            if ($request->getParam('update_items')) {
                $model->updateQuoteItems($items);
            } elseif (!$isSaveAction) {
                $model->addProducts($items);
            }
        }

        $removeItemId = (int) $request->getParam('remove_item');
        if ($removeItemId) {
            $model->removeItem($removeItemId);
            $model->recollectQuote();
        }
    }

    /**
     * Collecting shipping rates and recollect totals.
     * Totals should be recollected after items or quote update
     */
    public function resolveRecollect(bool $isForce = false): void
    {
        $model = $this->getQuoteEditModel();
        if ($isForce
            || (!$model->getQuote()->isVirtual() && $this->getRequest()->getPost('collect_shipping_rates'))
        ) {
            $model->collectShippingRates();
        }
    }

    /**
     * @param array $items
     * @return array
     */
    public function processItemsFiles(array $items): array
    {
        foreach ($items as $id => $item) {
            $buyRequest = new DataObject($item);
            $params = ['files_prefix' => 'item_' . $id . '_'];
            $buyRequest = $this->productHelper->addParamsToBuyRequest($buyRequest, $params);
            if ($buyRequest->hasData()) {
                $items[$id] = $buyRequest->toArray();
            }
        }

        return $items;
    }
}
