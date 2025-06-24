<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote\Action;

use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Amasty\RequestQuote\Exception\QuoteAlreadyInCartException;
use Amasty\RequestQuote\Model\Quote\AdvancedMergeResult;
use Amasty\RequestQuote\Model\Quote\Frontend\GetAmastyQuote;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * @api
 * Move active cart items in active quote.
 */
class MoveInQuote
{
    /**
     * @var QuoteRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var GetAmastyQuote
     */
    private $getAmastyQuote;

    public function __construct(
        QuoteRepositoryInterface $quoteRepository,
        CartRepositoryInterface $cartRepository,
        GetAmastyQuote $getAmastyQuote
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->cartRepository = $cartRepository;
        $this->getAmastyQuote = $getAmastyQuote;
    }

    /**
     * @throws QuoteAlreadyInCartException
     * @throws NoSuchEntityException
     */
    public function execute(int $cartId, int $quoteId): AdvancedMergeResult
    {
        $cart = $this->cartRepository->getActive($cartId);
        if ($this->getAmastyQuote->execute($cart)) {
            throw new QuoteAlreadyInCartException(__('This is approved quote.'));
        }

        $quote = $this->quoteRepository->getActive($quoteId);

        $mergeResult = $quote->advancedMerge($cart, true, true);

        $this->quoteRepository->save($quote);
        $this->cartRepository->save($cart);

        return $mergeResult;
    }
}
