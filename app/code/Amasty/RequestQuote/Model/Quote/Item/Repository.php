<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote\Item;

/**
 * Class Repository
 *
 * use physical class instead of virtual type because https://github.com/magento/magento2/issues/14950
 */
class Repository extends \Magento\Quote\Model\Quote\Item\Repository
{
    public const VERSION234 = '2.3.4';

    //phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
    public function __construct(
        \Amasty\RequestQuote\Api\QuoteRepositoryInterface $quoteRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Quote\Api\Data\CartItemInterfaceFactory $itemDataFactory,
        \Magento\Quote\Model\Quote\Item\CartItemOptionsProcessor $cartItemOptionsProcessor,
        array $cartItemProcessors = []
    ) {
        parent::__construct(
            $quoteRepository,
            $productRepository,
            $itemDataFactory,
            $cartItemOptionsProcessor,
            $cartItemProcessors
        );
    }
}
