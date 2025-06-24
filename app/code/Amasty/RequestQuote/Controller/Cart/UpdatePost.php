<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Controller\Cart;

use Amasty\RequestQuote\Helper\Data;
use Amasty\RequestQuote\Model\Quote\Frontend\UpdateQuoteItems;
use Amasty\RequestQuote\Model\Quote\Frontend\UpdateQuoteItems\UpdateRequestedPrice;
use Amasty\RequestQuote\Model\RegistryConstants;
use Amasty\RequestQuote\Model\Source\CustomerNotificationTemplates;
use Amasty\RequestQuote\Model\Source\Status;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validator\Exception as ValidatorException;

class UpdatePost extends \Amasty\RequestQuote\Controller\Cart
{
    /**
     * @return void
     */
    protected function _emptyShoppingCart()
    {
        try {
            $this->cart->truncate()->save();
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage($exception, __('We can\'t update the shopping cart.'));
        }
    }

    /**
     * @return bool
     */
    protected function _updateShoppingCart()
    {
        $result = false;
        try {
            $quote = $this->getCheckoutSession()->getQuote();
            $remarks = $this->getRequest()->getParam('remarks', null);
            if ($remarks && trim($remarks)) {
                $quote->setRemarks($this->cartHelper->prepareCustomerNoteForSave($remarks));
            }

            $cartData = $this->getRequest()->getParam('cart');
            if (is_array($cartData)) {
                $quoteItems = $this->getUpdateQuoteItems()->execute($this->cart->getQuote(), $cartData);

                if (!$this->cart->getCustomerSession()->getCustomerId()
                    && $this->cart->getQuote()->getCustomerId()
                ) {
                    $this->cart->getQuote()->setCustomerId(null);
                }

                $cartData = $this->cart->suggestItemsQty($cartData);
                $this->cart->updateItems($cartData);
                $this->cart->getQuote()->collectTotals();

                $this->getUpdateRequestedPrice()->execute($quoteItems);

                $this->cart->save();
                $result = true;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage(
                $this->getEscaper()->escapeHtml($e->getMessage())
            );
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t update the shopping cart.'));
        }

        return $result;
    }

    /**
     * @param $price
     * @return float|int
     */
    private function convertPriceToBase($price)
    {
        $store = $this->getCheckoutSession()->getQuote()->getStore();
        $rate = $store->getBaseCurrency()->getRate(
            $this->priceCurrency->getCurrency($store)
        );
        if ($rate != 1) {
            $price = (float)$price / (float)$rate;
        }

        return $price;
    }

    /**
     * @param $price
     * @return float
     */
    private function convertPriceToCurrent($price)
    {
        return $this->priceCurrency->convert($price);
    }

    /**
     * @return void
     */
    protected function submitAction()
    {
        $quote = $this->checkoutSession->getQuote();

        $this->_eventManager->dispatch('amasty_request_quote_submit_before', ['quote' => $quote]);

        /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
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
        $quote->save();
        $quote->setTotalsCollectedFlag(false)->collectTotals();
        $this->registry->register(RegistryConstants::AMASTY_QUOTE, $quote);
        $this->notifyCustomer();
        $this->notifyAdmin($quote->getId());
        $this->checkoutSession->setLastQuoteId($this->checkoutSession->getQuoteId());
        $this->checkoutSession->setQuoteId(null);

        $this->_eventManager->dispatch('amasty_request_quote_submit_after', ['quote' => $quote]);
    }

    private function notifyCustomer(): void
    {
        $quote = $this->checkoutSession->getQuote();
        $quote['created_date_formatted'] = $quote->getCreatedAtFormatted(\IntlDateFormatter::MEDIUM);
        $quote['submitted_date_formatted'] = $quote->getSubmitedDateFormatted(\IntlDateFormatter::MEDIUM);

        $email = $quote->getCustomerIsGuest()
            ? $quote->getCustomerEmail()
            : $this->getCustomerSession()->getCustomer()->getEmail();

        $bccEmails = $this->scopeConfig->getValue(
            'amasty_request_quote/customer_notifications/submit_email_copy_to',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $quote->getStoreId()
        );

        if ($bccEmails) {
            $bccEmails = explode(',', $bccEmails);
        }


        $this->emailSender->sendEmail(
            Data::CONFIG_PATH_CUSTOMER_SUBMIT_EMAIL,
            $email,
            [
                'viewUrl' => $this->urlResolver->getViewUrl((int)$this->checkoutSession->getQuoteId()),
                'quote' => $quote,
                'remarks' => $this->cartHelper->retrieveCustomerNote(
                    $this->checkoutSession->getQuote()->getRemarks()
                )
            ],
            CustomerNotificationTemplates::SUBMITTED_QUOTE,
            $bccEmails
        );
    }

    /**
     * @param int $quoteId
     */
    private function notifyAdmin($quoteId)
    {
        if ($this->getConfigHelper()->isAdminNotificationsInstantly()) {
            $this->getAdminNotification()->sendNotification([$quoteId]);
        }
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $backUrl = null;

        $updateAction = (string)$this->getRequest()->getParam('update_cart_action');

        switch ($updateAction) {
            case 'empty_cart':
                $this->_emptyShoppingCart();
                break;
            case 'update_qty':
                $this->_updateShoppingCart();
                break;
            case 'submit':
                if ($this->_updateShoppingCart()) {
                    if (!$this->getConfigHelper()->isLoggedIn() && !$this->processGuest()) {
                        break;
                    }

                    try {
                        $this->submitAction();
                    } catch (\Magento\Framework\Validator\Exception $exception) {
                        $this->messageManager->addErrorMessage($exception->getMessage());
                        return $this->_goBack($this->redirect->getRefererUrl());
                    }
                    $backUrl = $this->urlResolver->getSuccessUrl();
                }
                break;
            default:
                $this->_updateShoppingCart();
        }

        return $this->_goBack($backUrl);
    }

    private function processGuest(): bool
    {
        $email = $this->getRequest()->getParam('email');
        if (!$email) {
            $this->messageManager->addErrorMessage(__('"Email" is a required value.'));
            return false;
        }

        /*if (!$this->getQuoteAccountManagement()->isEmailAvailable($email)) {
            $this->messageManager->addErrorMessage(__('You already have an account with us. Please sign in.'));
            return false;
        }*/

        if ($this->getConfigProvider()->isAllowedCreateAccountForGuest() && $this->getQuoteAccountManagement()->isEmailAvailable($email)) {
            try {
                $this->login();
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return false;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Something went wrong'));
                $this->getLogger()->error($e->getMessage());
                return false;
            }
        } else {
            try {
                $this->updateQuoteForGuest($email);
            } catch (ValidatorException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return false;
            }
        }

        return true;
    }

    /**
     * @throws LocalizedException
     * @throws InputException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function login()
    {
        $customer = $this->getCustomerExtractor()->extract('customer_account_create', $this->getRequest());
        /** @var CustomerInterface $customer */
        $customer = $this->getAccountManagement()->createAccount($customer);
        $this->_eventManager->dispatch(
            'customer_register_success',
            ['account_controller' => $this, 'customer' => $customer]
        );

        $confirmationStatus = $this->getAccountManagement()->getConfirmationStatus($customer->getId());
        if ($confirmationStatus === AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
            $this->messageManager->addComplexSuccessMessage(
                'confirmAccountSuccessMessage',
                [
                    'url' => $this->getCustomerUrl()->getEmailConfirmationUrl($customer->getEmail()),
                ]
            );
        }
        if ($customer && $this->authenticate($customer)) {
            $this->refresh($customer);
            $this->checkoutSession->getQuote()->setCustomer($customer);
        }
    }

    /**
     * @param CustomerInterface $customer
     *
     * @return bool
     */
    private function authenticate($customer)
    {
        $customerId = $customer->getId();
        if ($this->getAuthentication()->isLocked($customerId)) {
            $this->messageManager->addErrorMessage(__('The account is locked.'));
            return false;
        }

        $this->getAuthentication()->unlock($customerId);
        $this->_eventManager->dispatch('customer_data_object_login', ['customer' => $customer]);

        return true;
    }

    /**
     * @param CustomerInterface $customer
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    private function refresh($customer)
    {
        if ($customer && $customer->getId()) {
            $this->_eventManager->dispatch('amquote_customer_authenticated');
            $this->getCustomerSession()->setCustomerDataAsLoggedIn($customer);
            $this->getCustomerSession()->regenerateId();
            $this->getCheckoutSession()->loadCustomerQuote();

            if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                $metadata->setPath('/');
                $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
            }
        }
    }

    /**
     * TODO: inject via constructor
     */
    private function getUpdateQuoteItems(): UpdateQuoteItems
    {
        return ObjectManager::getInstance()->get(UpdateQuoteItems::class);
    }

    /**
     * TODO: inject via constructor
     */
    private function getUpdateRequestedPrice(): UpdateRequestedPrice
    {
        return ObjectManager::getInstance()->get(UpdateRequestedPrice::class);
    }

    /**
     * @throws ValidatorException
     */
    private function updateQuoteForGuest(string $email): void
    {
        $messages = [];

        $firstName = $this->getRequest()->getParam('firstname');
        if (!$firstName) {
            $messages['firstname'] = [__('"First Name" is a required value.')];
        }

        $lastName = $this->getRequest()->getParam('lastname');
        if (!$firstName) {
            $messages['lastname'] = [__('"Last Name" is a required value.')];
        }

        if ($messages) {
            throw new ValidatorException(null, null, $messages);
        }

        $quote = $this->getCheckoutSession()->getQuote();
        $quote->setCustomerLastname($lastName);
        $quote->setCustomerFirstname($firstName);
        $quote->setCustomerEmail($email);
        $quote->setCustomerIsGuest(true);
    }
}
