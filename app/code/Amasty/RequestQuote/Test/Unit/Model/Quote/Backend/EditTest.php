<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Test\Unit\Model\Quote\Backend;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Model\Quote\Backend\Edit;
use Amasty\RequestQuote\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\RequestQuote\Test\Unit\Traits\ReflectionTrait;

/**
 * Class EditTest
 *
 * @see Edit
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class EditTest extends \PHPUnit\Framework\TestCase
{
    use ObjectManagerTrait;
    use ReflectionTrait;

    /**
     * @var Edit
     */
    private $model;

    public function setUp(): void
    {
        $this->model = $this->getObjectManager()->getObject(
            Edit::class,
            [
                'jsonSerializer' => $this->getObjectManager()->getObject(\Magento\Framework\Serialize\Serializer\Json::class)
            ]
        );
    }

    /**
     * @covers Edit::updateQuoteData
     *
     * @dataProvider updateQuoteDataDataProvider
     *
     * @throws \ReflectionException
     */
    public function testUpdateQuoteData($editData, $expectedData)
    {
        $this->model->addData($editData);
        $quote = $this->getObjectManager()->getObject(\Magento\Framework\DataObject::class);
        $this->invokeMethod($this->model, 'updateQuoteData', [$quote]);
        foreach ($expectedData as $key => $value) {
            $this->assertEquals($value, $quote->getData($key));
        }
    }

    /**
     * Data provider for updateQuoteData test
     * @return array
     */
    public function updateQuoteDataDataProvider()
    {
        return [
            [
                [
                    QuoteInterface::DISCOUNT => 10
                ],
                [
                    QuoteInterface::DISCOUNT => 10,
                    'remarks' => '{"admin_note":"Additional Discount in amount of 10% was applied."}'
                ]
            ],
            [
                [
                    QuoteInterface::DISCOUNT => 0,
                    QuoteInterface::SURCHARGE => 100
                ],
                [
                    QuoteInterface::DISCOUNT => null,
                    QuoteInterface::SURCHARGE => 100,
                    'remarks' => '{"admin_note":"Additional Surcharge in amount of 100% was applied."}'
                ]
            ]
        ];
    }
}
