<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Plugin\Cart\Controller;

use Amasty\RequestQuote\Model\Cart;
use Amasty\RequestQuote\Model\Quote\Session;
use Magento\Checkout\Model\Sidebar;

class QuotePlugin
{
    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Sidebar
     */
    private $sidebar;

    public function __construct(Cart $cart, Session $session, Sidebar $sidebar)
    {
        $this->cart = $cart;
        $this->session = $session;
        $this->sidebar = $sidebar;
    }

    /**
     * @param $subject
     * @param $result
     * @return Session
     */
    public function afterGetCheckoutSession($subject, $result)
    {
        return $this->session;
    }

    /**
     * @param $subject
     * @param $result
     * @return Session
     */
    public function afterGetCartModel($subject, $result)
    {
        return $this->cart;
    }

    /**
     * @param $subject
     * @param $result
     * @return Session
     */
    public function afterGetSidebar($subject, $result)
    {
        return $this->sidebar;
    }
}
