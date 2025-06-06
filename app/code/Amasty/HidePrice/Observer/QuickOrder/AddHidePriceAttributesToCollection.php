<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Observer\QuickOrder;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddHidePriceAttributesToCollection implements ObserverInterface
{
    /**
     * event name: amasty_quickorder_collection_load_before
     */
    public function execute(Observer $observer): void
    {
        $collection = $observer->getCollection();

        if ($collection instanceof ProductCollection) {
            $collection->addAttributeToSelect('am_hide_price_mode');
            $collection->addAttributeToSelect('am_hide_price_customer_gr');
        }
    }
}
