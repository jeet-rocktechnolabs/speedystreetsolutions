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

namespace Magetrend\AbandonedCart\Observer;

use Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer;

/**
 * Observer after quote was saved
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class QuoteSaveBefore implements ObserverInterface
{
    /**
     * @var \Magetrend\AbandonedCart\Model\visitor
     */
    public $visitor;

    /**
     * SubscriberPlugin constructor.
     * @param \Magetrend\AbandonedCart\Model\Visitor $visitorModel
     */
    public function __construct(
        \Magetrend\AbandonedCart\Model\Visitor $visitorModel
    ) {
        $this->visitor = $visitorModel;
    }

    /**
     * Hook for customer login event
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(Observer $observer)
    {
        if (empty($observer->getQuote()->getVisitorHash())) {
            if ($hash = $this->visitor->getVisitorHash()) {
                $observer->getQuote()->setVisitorHash($hash);
            }
        }
    }
}
