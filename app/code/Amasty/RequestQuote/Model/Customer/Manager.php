<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Customer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\StoreManagerInterface;

class Manager
{
    /**
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    public function __construct(
        CustomerInterfaceFactory $customerFactory,
        ArrayManager $arrayManager,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->customerFactory = $customerFactory;
        $this->arrayManager = $arrayManager;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * @param array $data
     * @return CustomerInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function create(array $data)
    {
        $customer = $this->customerFactory->create();

        $account = (array) $this->arrayManager->get('account', $data);

        $this->dataObjectHelper->populateWithArray(
            $customer,
            $account,
            CustomerInterface::class
        );

        return $customer;
    }
}
