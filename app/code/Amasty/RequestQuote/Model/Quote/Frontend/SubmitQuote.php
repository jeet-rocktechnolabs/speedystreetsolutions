<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote\Frontend;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Amasty\RequestQuote\Model\Email\AdminNotification;
use Amasty\RequestQuote\Model\Quote\Frontend\SubmitQuote\SendNotifications;
use Amasty\RequestQuote\Model\Source\Status;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class SubmitQuote
{
    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var QuoteRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var SendNotifications
     */
    private $sendNotifications;

    public function __construct(
        EventManagerInterface $eventManager,
        DataObjectFactory $dataObjectFactory,
        DateTime $dateTime,
        QuoteRepositoryInterface $quoteRepository,
        SendNotifications $sendNotifications
    ) {
        $this->eventManager = $eventManager;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->dateTime = $dateTime;
        $this->quoteRepository = $quoteRepository;
        $this->sendNotifications = $sendNotifications;
    }

    public function execute(QuoteInterface $quote): void
    {
        $this->eventManager->dispatch('amasty_request_quote_submit_before', ['quote' => $quote]);

        /** @var QuoteItem $quoteItem */
        foreach ($quote->getAllItems() as $quoteItem) {
            $priceOption = $this->dataObjectFactory->create(
                []
            )->setCode(
                'amasty_quote_price'
            )->setValue(
                $quoteItem->getPrice()
            )->setProduct(
                $quoteItem->getProduct()
            );
            $quoteItem->addOption($priceOption)->saveItemOptions();
        }

        $quote->setSubmitedDate($this->dateTime->gmtDate());
        $quote->setStatus(Status::PENDING);
        $quote->setData(QuoteInterface::ADMIN_NOTIFICATION_SEND, AdminNotification::NOT_SENT);

        $this->quoteRepository->save($quote);
        $this->sendNotifications->execute($quote);

        $this->eventManager->dispatch('amasty_request_quote_submit_after', ['quote' => $quote]);
    }
}
