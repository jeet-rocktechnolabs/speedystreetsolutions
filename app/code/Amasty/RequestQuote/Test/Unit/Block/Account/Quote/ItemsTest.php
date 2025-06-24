<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Test\Unit\Block\Account\Quote;

use Amasty\RequestQuote\Api\Data\QuoteItemInterface;
use Amasty\RequestQuote\Block\Account\Quote\Items;
use Amasty\RequestQuote\Model\Source\Status;
use Amasty\RequestQuote\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\RequestQuote\Test\Unit\Traits\ReflectionTrait;
use Magento\Framework\DataObject;

/**
 * Class ItemsTest
 *
 * @see Items
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class ItemsTest extends \PHPUnit\Framework\TestCase
{
    use ObjectManagerTrait;
    use ReflectionTrait;

    /**
     * @var Items
     */
    private $block;

    public function setUp(): void
    {
        $this->block = $this->createPartialMock(Items::class, ['getQuote', 'getOriginalPrice']);
    }

    /**
     * @covers Items::isApprovedPriceShowed
     *
     * @dataProvider isApprovedPriceShowedDataProvider
     *
     * @throws \ReflectionException
     */
    public function testIsApprovedPriceShowed($status, $expectedResult)
    {
        $quote = $this->getObjectManager()->getObject(DataObject::class, ['data' => [
            'status' => $status
        ]]);
        $this->block->expects($this->once())->method('getQuote')->willReturn($quote);

        $actualResult = $this->block->isApprovedPriceShowed();
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @covers Items::updateItemPrices
     *
     * @dataProvider updateItemPricesDataProvider
     *
     * @throws \ReflectionException
     */
    public function testUpdateItemPrices($itemData, $originalPrice, $notesData, $expectedAmount, $expectedPercent)
    {
        $quoteItem = $this->getObjectManager()->getObject(DataObject::class, ['data' => $itemData]);
        $itemNotes = $this->getObjectManager()->getObject(DataObject::class, ['data' => $notesData]);
        $this->block->expects($this->once())->method('getOriginalPrice')->willReturn($originalPrice);

        $this->invokeMethod($this->block, 'updateItemPrices', [$quoteItem, $itemNotes]);

        $this->assertEquals($expectedAmount, $quoteItem->getQuoteDiscountAmount());
        $this->assertEquals($expectedPercent, $quoteItem->getQuoteDiscountPercent());
    }

    /**
     * Data provider for isApprovedPriceShowed test
     * @return array
     */
    public function isApprovedPriceShowedDataProvider()
    {
        return [
            [Status::EXPIRED, true],
            [Status::APPROVED, true],
            [Status::ADMIN_NEW, false],
            [Status::CREATED, false]
        ];
    }

    /**
     * Data provider for updateItemPrices test
     * @return array
     */
    public function updateItemPricesDataProvider()
    {
        return [
            [
                ['price' => 90, 'qty' => 1],
                100,
                [QuoteItemInterface::REQUESTED_PRICE => 95],
                10,
                10
            ],
            [
                ['price' => 90, 'qty' => 3],
                100,
                [QuoteItemInterface::REQUESTED_PRICE => 95],
                30,
                10
            ]
        ];
    }
}
