<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Email;

use Amasty\Base\Model\Serializer;
use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Helper\Data;
use Amasty\RequestQuote\Helper\Date as DateHelper;
use Amasty\RequestQuote\Model\Notifications\IsNotificationEnabled;
use Amasty\RequestQuote\Model\Pdf\PdfProvider;
use Amasty\RequestQuote\Model\Source\CustomerNotificationTemplates;
use Amasty\RequestQuote\Model\UrlResolver;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Sender
{
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SessionFactory
     */
    private $customerSessionFactory;

    /**
     * @var Emulation
     */
    private $storeEmulation;

    /**
     * @var DateHelper
     */
    private $dateHelper;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var PdfProvider
     */
    private $pdfProvider;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var IsNotificationEnabled
     */
    private $isNotificationEnabled;

    /**
     * @var UrlResolver
     */
    private $urlResolver;

    public function __construct(
        Data $helper,
        DateHelper $dateHelper,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        SessionFactory $customerSessionFactory,
        Emulation $storeEmulation,
        Registry $registry,
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        Serializer $serializer,
        PdfProvider $pdfProvider,
        LayoutInterface $layout,
        IsNotificationEnabled $isNotificationEnabled,
        UrlResolver $urlResolver
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->storeEmulation = $storeEmulation;
        $this->dateHelper = $dateHelper;
        $this->registry = $registry;
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
        $this->pdfProvider = $pdfProvider;
        $this->layout = $layout;
        $this->isNotificationEnabled = $isNotificationEnabled;
        $this->urlResolver = $urlResolver;
    }

    /**
     * @param string $configPath Ex: amasty_request_quote/admin_notifications/notify_template
     * @param string|null $sendTo
     * @param array $data
     * @param int|null $notificationTemplateId
     * @param array|null $bccEmails
     * @return void
     */
    public function sendEmail(
        string $configPath,
        string $sendTo = null,
        array $data = [],
        int $notificationTemplateId = null,
        array $bccEmails = null
    ): void {
        if ($notificationTemplateId !== null && !$this->isNotificationEnabled->execute($notificationTemplateId)) {
            return;
        }

        $senderEmail = null;
        $configParts = explode('/', $configPath);
        $store = $this->storeManager->getStore();

        if (isset($configParts[1])) {
            $senderEmail = $this->helper->getSenderEmail($configParts[1], $store->getCode());

            if (!$sendTo) {
                $sendTo = $this->helper->getSendToEmail($configParts[1]);

                if ($sendTo && strpos($sendTo, ',') !== false) {
                    $sendTo = explode(',', $sendTo);
                }
            }
        }

        if ($senderEmail && $sendTo) {
            $defaultData = [
                'store' => $store,
                'customerName' => $this->getCustomerSession()->getCustomer()->getName()
            ];

            if (isset($data['quote']) && $data['quote']->getCustomerIsGuest()) {
                $configPath = str_replace(
                    'customer_notifications',
                    'guest_notifications',
                    $configPath
                );
                $defaultData['customerName'] = $data['quote']->getCustomerFirstname()
                    . ' ' . $data['quote']->getCustomerLastname();
            }

            $mailTemplateId = $this->scopeConfig->getValue(
                $configPath,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ) ?: str_replace('/', '_', $configPath);

            try {
                $transport = $this->transportBuilder->setTemplateIdentifier(
                    $mailTemplateId
                )->setTemplateModel(
                    Template::class
                )->setTemplateOptions(
                    ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $store->getId()]
                )->setTemplateVars(
                    array_merge($defaultData, $data)
                )->setFrom(
                    $senderEmail
                )->addTo(
                    $sendTo
                );
 
                if ($bccEmails) {
                    $transport->addTo($bccEmails);
                }

                if ($this->helper->isPdfAttach()
                    && $configPath == Data::CONFIG_PATH_CUSTOMER_APPROVE_EMAIL
                ) {
                    $this->layout->getUpdate()->load('amasty_quote_quote_pdf');
                    $this->layout->generateXml();
                    $this->layout->generateElements();
                    $pdfText = $this->pdfProvider->generatePdfText();
                    $transport->addAttachment($pdfText, $data['quote']->getIncrementId());
                }
                $transport->getTransport()->sendMessage();
            } catch (\Exception $exception) {
                $this->logger->critical($exception);
            }
        }
    }

    private function getCustomerSession(): Session
    {
        return $this->customerSessionFactory->create();
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl(string $route, array $params = []): string
    {
        return $this->helper->getUrl($route, $params);
    }

    private function sendQuoteEmail(Quote $quote, string $emailTemplate, int $notificationTemplateId = null): Sender
    {
        $bccEmails = $this->scopeConfig->getValue(
            'amasty_request_quote/customer_notifications/submit_email_copy_to',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $quote->getStoreId()
        );

        if ($bccEmails) {
            $bccEmails = explode(',', $bccEmails);
        }

        $this->storeEmulation->startEnvironmentEmulation($quote->getStoreId());
        $this->helper->clearScopeUrl();

        if ($quote->getCustomerIsGuest()) {
            $customerName = $quote->getCustomerFirstname() . ' ' . $quote->getCustomerLastname();
        } else {
            $customerName = $quote->getCustomerName();
        }

        $this->sendEmail(
            $emailTemplate,
            $quote->getCustomerEmail(),
            [
                'viewUrl' => $this->urlResolver->getViewUrl((int) $quote->getId(), ['_nosid' => true]),
                'quote' => $quote,
                'customerName' => $customerName,
                'store' => $quote->getStore(),
                'expiredDate' => $this->getExpiredDate($quote),
                'remarks' => $this->retrieveCustomerNote($quote->getRemarks()),
                'adminRemarks' => $this->retrieveAdminNote($quote->getRemarks())
            ],
            $notificationTemplateId,
            $bccEmails
        );
        $this->storeEmulation->stopEnvironmentEmulation();
        $this->helper->clearScopeUrl();

        return $this;
    }

    /**
     * @param Quote $quote
     *
     * @return $this
     */
    public function sendQuoteEditEmail(Quote $quote): Sender
    {
        if ($quote->getAllItems()) {
            $this->sendQuoteEmail(
                $quote,
                Data::CONFIG_PATH_CUSTOMER_EDIT_EMAIL,
                CustomerNotificationTemplates::MODIFIED_QUOTE
            );
        }

        return $this;
    }

    /**
     * @param Quote $quote
     *
     * @return $this
     */
    public function sendNewQuoteEmail(Quote $quote): Sender
    {
        $this->sendQuoteEmail($quote, Data::CONFIG_PATH_CUSTOMER_NEW_EMAIL);

        return $this;
    }

    /**
     * @param Quote $quote
     *
     * @return $this
     */
    public function sendApproveEmail(\Magento\Quote\Model\Quote $quote)
    {
        $this->sendQuoteEmail(
            $quote,
            Data::CONFIG_PATH_CUSTOMER_APPROVE_EMAIL,
            CustomerNotificationTemplates::APPROVED_QUOTE
        );

        return $this;
    }

    /**
     * @param Quote $quote
     * @return $this
     */
    public function sendDeclineEmail(Quote $quote): Sender
    {
        $this->sendQuoteEmail(
            $quote,
            Data::CONFIG_PATH_CUSTOMER_CANCEL_EMAIL,
            CustomerNotificationTemplates::CANCELED_QUOTE
        );

        return $this;
    }

    /**
     * @param Quote $quote
     * @return $this
     */
    public function sendExpiredEmail(Quote $quote): Sender
    {
        $this->sendQuoteEmail(
            $quote,
            Data::CONFIG_PATH_CUSTOMER_EXPIRED_EMAIL,
            CustomerNotificationTemplates::EXPIRED_QUOTE
        );

        return $this;
    }

    /**
     * @param Quote $quote
     * @return $this
     */
    public function sendReminderEmail(Quote $quote): Sender
    {
        $this->registry->register('amasty_quote_currency', $quote->getQuoteCurrencyCode());
        $this->sendQuoteEmail(
            $quote,
            Data::CONFIG_PATH_CUSTOMER_REMINDER_EMAIL,
            CustomerNotificationTemplates::REMINDER
        );
        $this->registry->unregister('amasty_quote_currency');

        return $this;
    }

    private function getExpiredDate(Quote $quote): ?string
    {
        $result = null;

        if ($this->helper->getExpirationTime() !== null && $quote->getExpiredDate()) {
            $result = $this->dateHelper->formatDate($quote->getExpiredDate());
        }

        return $result;
    }

    private function retrieveCustomerNote(?string $remarks): string
    {
        $additionalData = $this->serializer->unserialize($remarks);

        return $additionalData[QuoteInterface::CUSTOMER_NOTE_KEY] ?? '';
    }

    private function retrieveAdminNote(?string $remarks): string
    {
        $additionalData = $this->serializer->unserialize($remarks);

        return $additionalData[QuoteInterface::ADMIN_NOTE_KEY] ?? '';
    }
}
