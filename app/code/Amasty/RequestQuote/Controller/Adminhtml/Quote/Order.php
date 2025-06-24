<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Controller\Adminhtml\Quote;

use Amasty\RequestQuote\Model\Quote\Backend\Session as AmastySessionQuote;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session\Quote as MageSessionQuote;
use Magento\Customer\Api\CustomerRepositoryInterface;

class Order extends \Magento\Backend\App\Action
{
    public const ADMIN_RESOURCE = 'Amasty_RequestQuote::order';

    /**
     * @var MageSessionQuote
     */
    private $mageSessionQuote;

    /**
     * @var AmastySessionQuote
     */
    private $amSessionQuote;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    public function __construct(
        Context $context,
        MageSessionQuote $mageSessionQuote,
        AmastySessionQuote $amSessionQuote,
        CustomerRepositoryInterface $customerRepository
    ) {
        parent::__construct($context);
        $this->mageSessionQuote = $mageSessionQuote;
        $this->amSessionQuote = $amSessionQuote;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->isValidPostRequest()) {
            $this->messageManager->addErrorMessage(__('You can not create the order.'));
            return $resultRedirect->setPath('amasty_quote/*/');
        }
        $quote = $this->amSessionQuote->getQuote();
        if ($quote) {
            try {
                $this->customerRepository->getById($this->amSessionQuote->getCustomerId());
                $this->mageSessionQuote->setCustomerId($quote->getCustomerId());
                $this->mageSessionQuote->setStoreId($quote->getStoreId());
                $this->mageSessionQuote->setCurrencyId($quote->getQuoteCurrencyCode());
                $this->mageSessionQuote->setQuoteId($quote->getId());
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setRefererUrl();
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('You have not closed the quote.'));
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                return $resultRedirect->setRefererUrl();
            }
            return $resultRedirect->setPath('sales/order_create/index');
        }
        return $resultRedirect->setPath('amasty_quote/*/');
    }

    /**
     * @return bool
     */
    protected function isValidPostRequest()
    {
        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        $isPost = $this->getRequest()->isPost();
        return ($formKeyIsValid && $isPost);
    }
}
