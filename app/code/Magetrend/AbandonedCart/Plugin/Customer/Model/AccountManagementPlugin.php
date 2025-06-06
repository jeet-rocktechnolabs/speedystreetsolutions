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

namespace Magetrend\AbandonedCart\Plugin\Customer\Model;

/**
 * Newsletter subscriber plugin
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class AccountManagementPlugin
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
     * Cacth Email Address
     *
     * @param $subscriber
     * @param $email
     * @return array
     */
    public function beforeIsEmailAvailable($subject, $customerEmail, $websiteId = null)
    {
        $this->visitor->collectEmail($customerEmail, 1);
        return [$customerEmail, $websiteId];
    }
}
