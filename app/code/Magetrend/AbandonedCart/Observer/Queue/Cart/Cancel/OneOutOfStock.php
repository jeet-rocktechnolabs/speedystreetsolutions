<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.3 or later
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */

namespace Magetrend\AbandonedCart\Observer\Queue\Cart\Cancel;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magetrend\AbandonedCart\Model\Config\Source\CancelEvent;
use Magetrend\AbandonedCart\Model\Rule;
use Magetrend\AbandonedCart\Model\Queue;

/**
 * Cart rule email cancel observer, check is some product went out of stock
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class OneOutOfStock implements ObserverInterface
{
    /**
     * @var \Magetrend\AbandonedCart\Helper\Data
     */
    public $moduleHelper;

    public $stockRegistry;

    /**
     * NewCart constructor.
     * @param \Magetrend\AbandonedCart\Helper\Data $moduleHelper
     */
    public function __construct(
        \Magetrend\AbandonedCart\Helper\Data $moduleHelper,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    ) {
        $this->moduleHelper = $moduleHelper;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Look up for new cart, and cancel message if new cart is created.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(Observer $observer)
    {
        $rule = $observer->getRule();
        /**
         * @var \Magetrend\AbandonedCart\Model\Queue $queue
         */
        $queue = $observer->getQueue();
        if (!$queue->canSend() || !in_array(CancelEvent::EVENT_OUT_OF_STOCK, $rule->getCancelEvents())) {
            return;
        }
        /**
         * @var \Magento\Quote\Model\Quote $quote
         */
        $quote = $observer->getQuote();

        foreach ($quote->getAllItems() as $item) {
            $stock = $this->stockRegistry->getStockItem($item->getProductId());
            if ($stock->getIsInStock() == \Magento\CatalogInventory\Model\Stock::STOCK_OUT_OF_STOCK) {
                $queue->updateStatus(Queue::STATUS_PRODUCT_OUT_OF_STOCK);
                return;
            }
        }
    }
}
