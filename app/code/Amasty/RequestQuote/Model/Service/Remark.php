<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Service;

/**
 * @deprecated
 * @see \Amasty\RequestQuote\Api\GuestQuoteManagementInterface::updateCustomerNote
 * @see \Amasty\RequestQuote\Api\QuoteManagementInterface::updateCustomerNote
 */
class Remark
{
    /**
     * @var \Amasty\RequestQuote\Model\Quote\Session
     */
    private $checkoutSession;

    /**
     * @var \Amasty\RequestQuote\Helper\Cart
     */
    private $cartHelper;

    public function __construct(
        \Amasty\RequestQuote\Model\Quote\Session $checkoutSession,
        \Amasty\RequestQuote\Helper\Cart $cartHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->cartHelper = $cartHelper;
    }

    /**
     * @param string $remark
     *
     * @return void
     */
    public function save($remark)
    {
        $remark = $this->cartHelper->prepareCustomerNoteForSave($remark);
        $this->checkoutSession->getQuote()->setRemarks($remark)->save();
    }
}
