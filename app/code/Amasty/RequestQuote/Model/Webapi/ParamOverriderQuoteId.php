<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Webapi;

use Amasty\RequestQuote\Api\QuoteManagementInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Rest\Request\ParamOverriderInterface;

/**
 * Replaces a "%amasty_quote_id%" value with the current authenticated customer's quote cart
 */
class ParamOverriderQuoteId implements ParamOverriderInterface
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var QuoteManagementInterface
     */
    private $quoteManagement;

    public function __construct(
        UserContextInterface $userContext,
        QuoteManagementInterface $quoteManagement
    ) {
        $this->userContext = $userContext;
        $this->quoteManagement = $quoteManagement;
    }

    /**
     * @return int|null
     * @throws NoSuchEntityException
     */
    public function getOverriddenValue()
    {
        try {
            if ($this->userContext->getUserType() === UserContextInterface::USER_TYPE_CUSTOMER) {
                return (int)$this->quoteManagement->getQuoteCartForCustomer(
                    (int)$this->userContext->getUserId()
                )->getId();
            }
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException(__('Current customer does not have an active cart.'));
        }

        return null;
    }
}
