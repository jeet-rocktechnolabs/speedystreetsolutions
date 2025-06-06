<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Test\Unit\Model\Quote\Item;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Model\Quote\Item\Updater;
use Amasty\RequestQuote\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\RequestQuote\Test\Unit\Traits\ReflectionTrait;

/**
 * Class UpdaterTest
 *
 * @see Updater
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class UpdaterTest extends \PHPUnit\Framework\TestCase
{
    use ObjectManagerTrait;
    use ReflectionTrait;

    /**
     * @var Updater
     */
    private $model;

    public function setUp(): void
    {
        $this->model = $this->getObjectManager()->getObject(Updater::class);
    }

    /**
     * @covers Updater::applyPriceModificators
     *
     * @dataProvider applyPriceModificatorsDataProvider
     *
     * @throws \ReflectionException
     */
    public function testApplyPriceModificators($price, $modificators, $expectedPrice)
    {
        $resultPrice = $this->invokeMethod($this->model, 'applyPriceModificators', [$price, $modificators]);
        $this->assertEquals($expectedPrice, $resultPrice);
    }

    /**
     * Data provider for applyPriceModificators test
     * @return array
     */
    public function applyPriceModificatorsDataProvider()
    {
        return [
            [
                30,
                [QuoteInterface::SURCHARGE => 10],
                33
            ],
            [
                30,
                [QuoteInterface::DISCOUNT => 10],
                27
            ],
            [
                30,
                [QuoteInterface::SURCHARGE => 10, QuoteInterface::DISCOUNT => 10],
                33
            ],
            [
                30,
                [QuoteInterface::SURCHARGE => null, QuoteInterface::DISCOUNT => 10],
                27
            ]
        ];
    }
}
