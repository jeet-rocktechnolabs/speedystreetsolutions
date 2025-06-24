<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Test\Unit\Model\CheckoutConfigProvider;

use Amasty\RequestQuote\Api\Data\QuoteInterface as AmastyQuoteInterface;
use Amasty\RequestQuote\Model\CheckoutConfigProvider\ShippingAddress;
use Amasty\RequestQuote\Model\Customer\Address\CustomerAddressDataFormatter as AmastyAddressDataFormatter;
use Amasty\RequestQuote\Model\Customer\Address\CustomerAddressDataFormatterFactory;
use Amasty\RequestQuote\Model\Quote\Frontend\GetAmastyQuote;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Address\CustomerAddressDataFormatter as MagentoAddressDataFormatter;
use Magento\Quote\Model\Quote as MagentoQuote;
use Magento\Quote\Model\Quote\Address;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @see ShippingAddress
 */
class ShippingAddressTest extends TestCase
{
    /**
     * @covers ShippingAddress::getConfig
     *
     * @dataProvider getConfigDataProvider
     */
    public function testGetConfig(bool $isAmastyQuote, bool $shippingConfigured, array $address, array $expected)
    {
        $getAmastyQuote = $this->createMock(GetAmastyQuote::class);
        $getAmastyQuote->expects($this->once())->method('execute')->willReturnCallback(
            function () use ($isAmastyQuote, $shippingConfigured) {
                if ($isAmastyQuote) {
                    $amastyQuote = $this->createMock(AmastyQuoteInterface::class);
                    $amastyQuote->expects($this->once())->method('isShippingConfigure')->willReturn(
                        $shippingConfigured
                    );
                    return $amastyQuote;
                } else {
                    return null;
                }
            }
        );

        $customerAddressDataFormatterFactory = $this->createMock(CustomerAddressDataFormatterFactory::class);
        if (class_exists(MagentoAddressDataFormatter::class)) {
            $customerAddressDataFormatter = $this->createMock(MagentoAddressDataFormatter::class);
        } else {
            $customerAddressDataFormatter = $this->createMock(AmastyAddressDataFormatter::class);
        }
        $customerAddressDataFormatterFactory->expects($this->any())->method('create')->willReturn(
            $customerAddressDataFormatter
        );
        $customerAddressDataFormatter->expects($this->any())->method('prepareAddress')->willReturn($address);

        $customerAddress = $this->createMock(\Magento\Customer\Api\Data\AddressInterface::class);

        $shippingAddress = $this->createMock(Address::class);
        $shippingAddress->expects($this->any())->method('exportCustomerAddress')->willReturn($customerAddress);

        $magentoQuote = $this->createMock(MagentoQuote::class);
        $magentoQuote->expects($this->any())->method('getShippingAddress')->willReturn($shippingAddress);
        $checkoutSession = $this->createMock(CheckoutSession::class);
        $checkoutSession->expects($this->any())->method('getQuote')->willReturn($magentoQuote);

        $logger = $this->createMock(LoggerInterface::class);

        $model = new ShippingAddress($getAmastyQuote, $checkoutSession, $customerAddressDataFormatterFactory, $logger);

        $actual = $model->getConfig();

        $this->assertEquals($expected, $actual);
    }

    public function getConfigDataProvider(): array
    {
        return [
            [
                true,
                false,
                [],
                []
            ],
            [
                true,
                true,
                [
                    'city' => 'vicecity'
                ],
                [
                    'amasty_quote' => [
                        'shipping_address' => [
                            'city' => 'vicecity'
                        ]
                    ]
                ]
            ],
            [
                true,
                false,
                [
                    'city' => 'vicecity'
                ],
                []
            ]
        ];
    }
}
