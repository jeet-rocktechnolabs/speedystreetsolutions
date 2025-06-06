<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Test\Unit\Model\Email;

use Amasty\Base\Model\Serializer;
use Amasty\RequestQuote\Helper\Data;
use Amasty\RequestQuote\Helper\Date as DateHelper;
use Amasty\RequestQuote\Model\ConfigProvider;
use Amasty\RequestQuote\Model\Email\Sender;
use Amasty\RequestQuote\Model\Email\TransportBuilder;
use Amasty\RequestQuote\Model\Notifications\IsNotificationEnabled;
use Amasty\RequestQuote\Model\Pdf\PdfProvider;
use Amasty\RequestQuote\Model\Quote;
use Amasty\RequestQuote\Model\UrlResolver;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Layout\ProcessorInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @see Sender
 */
class SenderTest extends TestCase
{
    /**
     * @var bool
     */
    private $isSendMessageCalled = false;

    /**
     * @covers Sender::sendEmail
     * @dataProvider sendEmailProvider
     *
     * @param string $configPath
     * @param string $sendTo
     * @param array $data
     * @param bool $isNotificationEnabled
     * @param bool $isPdfAttach
     * @param bool $isSendMessageCalled
     * @return void
     */
    public function testSendEmail(
        string $configPath,
        string $sendTo,
        array $data,
        bool $isNotificationEnabled,
        bool $isPdfAttach,
        bool $isSendMessageCalled
    ): void {
        $dataHelper = $this->createMock(Data::class);
        $dateHelper = $this->createMock(DateHelper::class);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $transportBuilder = $this->createMock(TransportBuilder::class);
        $sessionFactory = $this->createMock(SessionFactory::class);
        $registry = $this->createMock(Registry::class);
        $logger = $this->createMock(LoggerInterface::class);
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $serializer = $this->createMock(Serializer::class);
        $pdfProvider = $this->createMock(PdfProvider::class);
        $layout = $this->createMock(LayoutInterface::class);
        $notificationStatusChecker = $this->createMock(IsNotificationEnabled::class);
        $emulation = $this->createMock(Emulation::class);
        $store = $this->createMock(StoreInterface::class);
        $session = $this->createMock(Session::class);
        $customer = $this->createMock(Customer::class);
        $layoutProcessor = $this->createMock(ProcessorInterface::class);
        $transport = $this->createMock(TransportInterface::class);
        $urlResolver = $this->createMock(UrlResolver::class);

        $quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->addMethods(['getIncrementId'])
            ->getMock();

        $data['quote'] = $quote;

        $notificationStatusChecker->expects($this->any())
            ->method('execute')
            ->willReturn($isNotificationEnabled);

        $storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($store);

        $store->expects($this->any())
            ->method('getCode')
            ->willReturn('default');

        $store->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $dataHelper->expects($this->any())
            ->method('getSenderEmail')
            ->willReturnArgument(0);

        $dataHelper->expects($this->any())
            ->method('getSendToEmail')
            ->willReturnArgument(0);

        $sessionFactory->expects($this->any())
            ->method('create')
            ->willReturn($session);

        $session->expects($this->any())
            ->method('getCustomer')
            ->willReturn($customer);

        $customer->expects($this->any())
            ->method('getName')
            ->willReturn('customer');

        $scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturnArgument(0);

        $dataHelper->expects($this->any())
            ->method('isPdfAttach')
            ->willReturn($isPdfAttach);

        $pdfProvider->expects($this->any())
            ->method('generatePdfText')
            ->willReturnArgument('text');

        $layout->expects($this->any())
            ->method('getUpdate')
            ->willReturn($layoutProcessor);

        $transportBuilder->expects($this->any())
            ->method('setTemplateIdentifier')
            ->willReturnSelf();

        $transportBuilder->expects($this->any())
            ->method('setTemplateModel')
            ->willReturnSelf();

        $transportBuilder->expects($this->any())
            ->method('setTemplateOptions')
            ->willReturnSelf();

        $transportBuilder->expects($this->any())
            ->method('setTemplateVars')
            ->willReturnSelf();

        $transportBuilder->expects($this->any())
            ->method('setFrom')
            ->willReturnSelf();

        $transportBuilder->expects($this->any())
            ->method('addTo')
            ->willReturnSelf();

        $transportBuilder->expects($this->any())
            ->method('getTransport')
            ->willReturn($transport);

        $transport->expects($this->any())
            ->method('sendMessage')
            ->willReturnCallback(
                function () {
                    $this->isSendMessageCalled = true;
                }
            );

        $quote->expects($this->any())
            ->method('getIncrementId')
            ->willReturn(3);

        $processor = new Sender(
            $dataHelper,
            $dateHelper,
            $storeManager,
            $transportBuilder,
            $sessionFactory,
            $emulation,
            $registry,
            $logger,
            $scopeConfig,
            $serializer,
            $pdfProvider,
            $layout,
            $notificationStatusChecker,
            $urlResolver
        );

        $processor->sendEmail($configPath, $sendTo, $data, 3);
        $this->assertEquals($isSendMessageCalled, $this->isSendMessageCalled);
    }

    /**
     * Data provider for sendEmail test
     * @return array
     */
    public function sendEmailProvider(): array
    {
        return [
            [ConfigProvider::CONFIG_PATH_CUSTOMER_REMINDER_EMAIL, 'customer', [], true, false, true],
            [ConfigProvider::CONFIG_PATH_CUSTOMER_EXPIRED_EMAIL, '', [], true, false, true],
            [ConfigProvider::CONFIG_PATH_CUSTOMER_CANCEL_EMAIL, 'customer', [], true, true, true],
            [ConfigProvider::CONFIG_PATH_CUSTOMER_APPROVE_EMAIL, 'customer', [], false, true, false]
        ];
    }
}
