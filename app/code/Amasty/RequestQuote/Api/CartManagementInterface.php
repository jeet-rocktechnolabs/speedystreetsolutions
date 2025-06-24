<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Api;

/**
 * Provide methods for manipulate with quote merged in cart.
 */
interface CartManagementInterface
{
    /**
     * @param int $cartId
     * @return bool
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function removeQuoteFromCart(int $cartId): bool;
}
