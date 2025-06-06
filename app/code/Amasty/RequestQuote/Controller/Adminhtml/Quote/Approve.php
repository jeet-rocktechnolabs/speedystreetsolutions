<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Controller\Adminhtml\Quote;

use Amasty\RequestQuote\Model\Quote;
use Amasty\RequestQuote\Model\Source\Status;
use Magento\Framework\Exception\LocalizedException;

class Approve extends \Amasty\RequestQuote\Controller\Adminhtml\Quote\ActionAbstract
{

    public const ADMIN_RESOURCE = 'Amasty_RequestQuote::approve';

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->isValidPostRequest()) {
            $this->messageManager->addErrorMessage(__('You have not approved the quote.'));
            return $resultRedirect->setPath('amasty_quote/*/');
        }
        $quote = $this->initQuote();
        if ($quote) {
            try {
                $this->validateQuote($quote);
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
                $this->quoteRepository->save($quote);

                if ($newQuote) {
                    $this->emailSender->sendApproveEmail($quote);
                }

                $this->messageManager->addSuccessMessage(__('You approved the quote.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('You have not approved the quote.'));
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            }

            return $resultRedirect->setPath('amasty_quote/quote/view', ['quote_id' => $quote->getId()]);
        }

        return $resultRedirect->setPath('amasty_quote/*/');
    }

    /**
     * @param Quote $quote
     * @throws LocalizedException
     */
    private function validateQuote(Quote $quote)
    {
        if (!$quote->getAllItems()) {
            throw new LocalizedException(__('Please specify quote items.'));
        }
    }
}
