<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote\Item\Query;

use Amasty\RequestQuote\Api\Data\QuoteItemInterface;
use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Amasty\RequestQuote\Model\Quote\Item\ConvertCartItem;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\Quote\Item\CartItemOptionsProcessor;

class GetListForQuote implements GetListForQuoteInterface
{
    /**
     * @var QuoteRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var CartItemOptionsProcessor
     */
    private $cartItemOptionsProcessor;

    /**
     * @var ConvertCartItem
     */
    private $convertCartItem;

    public function __construct(
        QuoteRepositoryInterface $quoteRepository,
        CartItemOptionsProcessor $cartItemOptionsProcessor,
        ConvertCartItem $convertCartItem
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->cartItemOptionsProcessor = $cartItemOptionsProcessor;
        $this->convertCartItem = $convertCartItem;
    }

    /**
     * @return QuoteItemInterface[]
     * @throws NoSuchEntityException
     */
    public function execute(int $quoteId): array
    {
        $output = [];

        $quote = $this->quoteRepository->get($quoteId);
        /** @var  QuoteItem $quoteItem */
        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            $this->cartItemOptionsProcessor->addProductOptions($quoteItem->getProductType(), $quoteItem);
            $this->cartItemOptionsProcessor->applyCustomOptions($quoteItem);
            $quoteItem = $this->convertCartItem->execute($quoteItem);
            $output[] = $quoteItem;
        }

        return $output;
    }
}
