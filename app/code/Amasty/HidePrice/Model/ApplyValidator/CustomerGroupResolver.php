<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Model\ApplyValidator;

use Magento\Customer\Model\Session as CustomerSession;

class CustomerGroupResolver
{
    /**
     * @var string|int
     */
    private $currentCustomerGroup = null;

    /**
     * @var CustomerSession
     */
    private $session;

    public function __construct(CustomerSession $session)
    {
        $this->session = $session;
    }

    public function get(): string
    {
        if ($this->currentCustomerGroup === null) {
            $this->currentCustomerGroup = $this->session->getCustomerGroupId();
            if (!$this->currentCustomerGroup) {
                $this->currentCustomerGroup = \Amasty\HidePrice\Helper\Data::NOT_LOGGED_KEY;
            }
        }

        return (string)$this->currentCustomerGroup;
    }
}
