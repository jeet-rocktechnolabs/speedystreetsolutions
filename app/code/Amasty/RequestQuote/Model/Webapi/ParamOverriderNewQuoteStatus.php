<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Webapi;

use Amasty\RequestQuote\Model\Source\Status;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Webapi\Rest\Request\ParamOverriderInterface;

class ParamOverriderNewQuoteStatus implements ParamOverriderInterface
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    public function __construct(UserContextInterface $userContext)
    {
        $this->userContext = $userContext;
    }

    /**
     * @return int
     */
    public function getOverriddenValue()
    {
        if (in_array($this->userContext->getUserType(), [
            UserContextInterface::USER_TYPE_ADMIN,
            UserContextInterface::USER_TYPE_INTEGRATION
        ])) {
            return Status::ADMIN_CREATED;
        }

        return Status::CREATED;
    }
}
