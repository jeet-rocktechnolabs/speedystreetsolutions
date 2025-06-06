<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Api;

interface AccountManagementInterface
{
    /**
     * Copy from magento code base,
     * because magento broken expected functionality with setting checkout/options/enable_guest_checkout_login
     * @see \Magento\Customer\Model\AccountManagement::isEmailAvailable
     *
     * @param string $customerEmail
     * @param int|null $websiteId
     * @return bool
     */
    public function isEmailAvailable(string $customerEmail, ?int $websiteId = null): bool;
}
