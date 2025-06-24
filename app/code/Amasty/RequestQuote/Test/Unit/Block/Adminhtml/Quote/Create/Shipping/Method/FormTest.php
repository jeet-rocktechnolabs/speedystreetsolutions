<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Test\Unit\Block\Adminhtml\Quote\Create\Shipping\Method;

use Amasty\RequestQuote\Block\Adminhtml\Quote\Create\Shipping\Method\Form;
use Amasty\RequestQuote\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\RequestQuote\Test\Unit\Traits\ReflectionTrait;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address\Rate;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class FormTest
 *
 * @see Form
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class FormTest extends \PHPUnit\Framework\TestCase
{
    use ObjectManagerTrait;
    use ReflectionTrait;

    /**
     * @covers Form::getActiveMethodRate
     *
     * @dataProvider getActiveMethodRateDataProvider
     *
     * @throws \ReflectionException
     */
    public function testGetActiveMethodRate($shippingCodes, $currentShippingMethod, $expected)
    {
        /** @var Form|MockObject $block */
        $block = $this->createPartialMock(Form::class, [
            'getShippingRates',
            'getShippingMethod'
        ]);
        $shippingRates = [];
        foreach ($shippingCodes as $shippingCode) {
            $shippingRates[] = [$this->getObjectManager()->getObject(Rate::class)->setCode($shippingCode)];
        }
        $block->expects($this->any())->method('getShippingRates')->willReturn($shippingRates);
        $block->expects($this->any())->method('getShippingMethod')->willReturn($currentShippingMethod);

        $actual = $block->getActiveMethodRate();
        if ($actual) {
            $actual = $actual->getCode();
        }

        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers Form::getCarrierName
     *
     * @dataProvider getCarrierNameDataProvider
     *
     * @throws \ReflectionException
     */
    public function testGetCarrierName($carrierCode, $rates, $nameFromConfig, $expected)
    {
        /** @var Form |MockObject $block */
        $block = $this->createPartialMock(Form::class, ['getStore']);

        $config = $this->createMock(\Magento\Framework\App\Config::class);
        $config->expects($this->any())->method('getValue')->willReturn($nameFromConfig);
        $this->setProperty($block, '_scopeConfig', $config, Form::class);

        $block->expects($this->any())->method('getStore')->willReturn($this->getObjectManager()->getObject(DataObject::class, ['data' => ['id' => 1]]));

        $result = $block->getCarrierName($carrierCode, $rates);

        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for getActiveMethodRate test
     * @return array
     */
    public function getActiveMethodRateDataProvider()
    {
        return [
            [
                [],
                null,
                false
            ],
            [
                ['test'],
                'test',
                'test'
            ],
            [
                ['test1', 'test2'],
                'test2',
                'test2'
            ]
        ];
    }

    /**
     * Data provider for getCarrierName test
     * @return array
     */
    public function getCarrierNameDataProvider()
    {
        return [
            [
                'test',
                [],
                'test',
                'test'
            ],
            [
                'test1',
                [
                    $this->getObjectManager()->getObject(DataObject::class, ['data' => ['carrier_title' => 'xxx']])
                ],
                'test',
                'xxx'
            ],
            [
                'yyy',
                [],
                '',
                'yyy'
            ]
        ];
    }
}
