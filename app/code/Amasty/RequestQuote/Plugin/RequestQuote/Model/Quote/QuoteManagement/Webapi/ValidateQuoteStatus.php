<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Plugin\RequestQuote\Model\Quote\QuoteManagement\Webapi;

use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Amasty\RequestQuote\Model\Quote\QuoteManagement;
use Amasty\RequestQuote\Model\Source\Status;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class ValidateQuoteStatus
{
    /**
     * @var QuoteRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    public function __construct(QuoteRepositoryInterface $quoteRepository, UserContextInterface $userContext)
    {
        $this->quoteRepository = $quoteRepository;
        $this->userContext = $userContext;
    }

    /**
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeAssignCustomer(QuoteManagement $quoteManagement, int $quoteId): void
    {
        $quote = $this->quoteRepository->get($quoteId);

        if (in_array($this->userContext->getUserType(), [
            UserContextInterface::USER_TYPE_ADMIN,
            UserContextInterface::USER_TYPE_INTEGRATION
        ])) {
            $activeStatus = Status::ADMIN_CREATED;
        } else {
            $activeStatus = Status::CREATED;
        }

        if ($quote->getStatus() !== $activeStatus) {
            throw NoSuchEntityException::singleField('quoteId', $quoteId);
        }
    }
}
