<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Controller\Account;

use Amasty\RequestQuote\Helper\Data;
use Amasty\RequestQuote\Model\Source\CustomerNotificationTemplates;
use Amasty\RequestQuote\Model\Source\Status;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\ResultFactory;

class Cancel extends AbstractAccount
{
    /**
     * @return ResultRedirect
     */
    public function execute()
    {
        try {
            $quote = $this->loadQuote();
            $quote->setStatus(Status::CANCELED);
            $quote->save();
            $this->notifyCustomer();
            $this->messageManager->addSuccessMessage(__('Request Quote closed'));
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__('Can\'t close Request Quote'));
        }
        /** @var ResultRedirect $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $result->setRefererUrl();

        return $result;
    }

    private function notifyCustomer(): void
    {
        $this->getEmailSender()->sendEmail(
            Data::CONFIG_PATH_CUSTOMER_CANCEL_EMAIL,
            $this->getCustomerSession()->getCustomer()->getEmail(),
            [
                'viewUrl' => $this->_url->getUrl(
                    'amasty_quote/account/view',
                    ['quote_id' => $this->getQuote()->getId()]
                ),
                'quote' => $this->getQuote()
            ],
            CustomerNotificationTemplates::CANCELED_QUOTE
        );
    }
}
