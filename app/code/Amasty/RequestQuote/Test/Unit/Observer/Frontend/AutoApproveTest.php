<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Test\Unit\Model\Frontend;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Observer\Frontend\AutoApprove;
use Amasty\RequestQuote\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\RequestQuote\Test\Unit\Traits\ReflectionTrait;
use Magento\Framework\DataObject;

/**
 * Class AutoApproveTest
 *
 * @see AutoApprove
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class AutoApproveTest extends \PHPUnit\Framework\TestCase
{
    use ObjectManagerTrait;
    use ReflectionTrait;

    /**
     * @var AutoApprove
     */
    private $model;

    public function setUp(): void
    {
        $this->model = $this->createPartialMock(AutoApprove::class, ['getOriginalPrice']);
    }

    /**
     * @covers AutoApprove::isAutoApproveAllowed
     *
     * @dataProvider isAutoApproveAllowedDataProvider
     *
     * @throws \ReflectionException
     */
    public function testIsAutoApproveAllowed($enabled, $percent, $itemsData, $expectedResult)
    {
        $config = $this->createMock(\Amasty\RequestQuote\Helper\Data::class);
        $config->expects($this->any())->method('isAutoApproveAllowed')->willReturn($enabled);
        $config->expects($this->any())->method('getAllowedPercentForApprove')->willReturn($percent);

        $this->model->expects($this->any())->method('getOriginalPrice')->willReturnCallback(function ($item) {
            return $item->getOriginalPrice();
        });

        $items = [];
        foreach ($itemsData as $itemData) {
            $items[] = $this->getObjectManager()->getObject(DataObject::class, [
                'data' => $itemData
            ]);
        }
        $quote = $this->getObjectManager()->getObject(DataObject::class, [
            'data' => [
                'all_visible_items' => $items
            ]
        ]);

        $this->setProperty($this->model, 'configHelper', $config, AutoApprove::class);

        $actualResult = $this->invokeMethod($this->model, 'isAutoApproveAllowed', [$quote]);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Data provider for isAutoApproveAllowed test
     * @return array
     */
    public function isAutoApproveAllowedDataProvider()
    {
        return [
            [
                1,
                50,
                [
                    [
                        'price' => 10,
                        'original_price' => 11
                    ],
                    [
                        'price' => 9,
                        'original_price' => 11
                    ]
                ],
                true
            ],
            [
                1,
                1,
                [
                    [
                        'price' => 10,
                        'original_price' => 11
                    ]
                ],
                false
            ],
            [
                0,
                1,
                [],
                false
            ],
            [
                1,
                1,
                [
                    [
                        'price' => 15,
                        'original_price' => 11
                    ]
                ],
                false
            ],
            [
                0,
                1,
                [
                    [
                        'price' => 10,
                        'original_price' => 11
                    ]
                ],
                false
            ]
        ];
    }
}
