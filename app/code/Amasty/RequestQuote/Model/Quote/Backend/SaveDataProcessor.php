<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote\Backend;

use Amasty\RequestQuote\Model\Source\Status;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Quote\Model\CustomerManagement;

class SaveDataProcessor
{
    /**
     * @var Edit
     */
    private $model;

    /**
     * @var CustomerManagement
     */
    private $customerManagement;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(Edit $model, CustomerManagement $customerManagement, DateTime $dateTime)
    {
        $this->model = $model;
        $this->customerManagement = $customerManagement;
        $this->dateTime = $dateTime;
    }

    public function postQuote(bool $isNewQuote = true)
    {
        $model = $this->model;
        $quote = $model->getQuote();

        if ($isNewQuote) {
            $model->prepareCustomer();
            $this->customerManagement->populateCustomerInfo($quote);
            $quote->setStatus(Status::ADMIN_CREATED);
            $quote->setSubmitedDate($this->dateTime->gmtDate());
        }

        return $model->saveFromQuote();
    }
}
