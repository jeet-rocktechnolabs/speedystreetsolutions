<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model;

use Amasty\RequestQuote\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class AccountManagement implements AccountManagementInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    public function __construct(StoreManagerInterface $storeManager, CustomerRepositoryInterface $customerRepository)
    {
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
    }

    public function isEmailAvailable(string $customerEmail, ?int $websiteId = null): bool
    {
        try {
            if ($websiteId === null) {
                $websiteId = $this->storeManager->getStore()->getWebsiteId();
            }
            $this->customerRepository->get($customerEmail, $websiteId);
            return false;
        } catch (NoSuchEntityException $e) {
            return true;
        }
    }
}
