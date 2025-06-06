<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Api;

/**
 * @deprecated
 * @see \Amasty\RequestQuote\Api\GuestQuoteManagementInterface::updateCustomerNote
 * @see \Amasty\RequestQuote\Api\QuoteManagementInterface::updateCustomerNote
 */
interface RemarkServiceInterface
{
    /**
     * @param string $remark
     *
     * @return void
     */
    public function save($remark);
}
