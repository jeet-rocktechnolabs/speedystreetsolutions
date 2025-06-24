<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Controller\Adminhtml\Quote;

use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Amasty\RequestQuote\Model\Quote;
use Amasty\RequestQuote\Model\Registry;
use Amasty\RequestQuote\Model\RegistryConstants;
use Amasty\RequestQuote\Model\Source\Status;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Amasty\RequestQuote\Model\ResourceModel\Quote\CollectionFactory;

class MassApprove extends \Amasty\RequestQuote\Controller\Adminhtml\Quote\AbstractMassAction
{
    public const ADMIN_RESOURCE = 'Amasty_RequestQuote::approve';

    /**
     * @var \Amasty\RequestQuote\Model\Email\Sender
     */
    private $emailSender;

    /**
     * @var \Amasty\RequestQuote\Helper\Data
     */
    private $configHelper;

    /**
     * @var \Amasty\RequestQuote\Helper\Date
     */
    private $dateHelper;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var QuoteRepositoryInterface
     */
    private $quoteRepository;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Amasty\RequestQuote\Model\Email\Sender $emailSender,
        \Amasty\RequestQuote\Helper\Data $configHelper,
        \Amasty\RequestQuote\Helper\Date $dateHelper,
        Registry $registry,
        QuoteRepositoryInterface $quoteRepository
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->emailSender = $emailSender;
        $this->configHelper = $configHelper;
        $this->dateHelper = $dateHelper;
        $this->registry = $registry;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $countUpdatedQuotes = 0;
        foreach ($collection->getAllIds() as $quoteId) {
            $quote = $this->quoteRepository->get((int) $quoteId, ['*']);
            if (!$quote->canApprove()) {
                continue;
            }

            $this->registerQuote($quote);
            if ($expDays = $this->configHelper->getExpirationTime()) {
                $quote->setExpiredDate($this->dateHelper->increaseDays($expDays));
            }
            if ($remDays = $this->configHelper->getReminderTime()) {
                $quote->setReminderDate($this->dateHelper->increaseDays($remDays));
            }

            if ($quote->getStatus() == Status::ADMIN_CREATED
                || $quote->getStatus() == Status::PENDING
            ) {
                $newQuote = true;
            } else {
                $newQuote = false;
            }

            $quote->setStatus(Status::APPROVED);
            $quote->save();

            if ($newQuote) {
                $this->emailSender->sendApproveEmail($quote);
            }

            $countUpdatedQuotes++;
        }
        $countNonUpdatedQuotes = $collection->count() - $countUpdatedQuotes;

        if ($countNonUpdatedQuotes && $countUpdatedQuotes) {
            $this->messageManager->addErrorMessage(__('%1 quote(s) cannot be updated.', $countNonUpdatedQuotes));
        } elseif ($countNonUpdatedQuotes) {
            $this->messageManager->addErrorMessage(__('You cannot update the quote(s).'));
        }

        if ($countUpdatedQuotes) {
            $this->messageManager->addSuccessMessage(__('%1 quote(s) have been updated.', $countUpdatedQuotes));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }

    private function registerQuote(Quote $quote): void
    {
        if ($this->registry->registry(RegistryConstants::AMASTY_QUOTE)) {
            $this->registry->unregister(RegistryConstants::AMASTY_QUOTE);
        }

        $this->registry->register(RegistryConstants::AMASTY_QUOTE, $quote);
    }
}
