<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote\Action;

use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Amasty\RequestQuote\Exception\NotApprovedQuoteException;
use Amasty\RequestQuote\Exception\QuoteAlreadyInCartException;
use Amasty\RequestQuote\Model\Quote\Frontend\GetAmastyQuote;
use Amasty\RequestQuote\Model\Quote\Move\MergeQuotes;
use Amasty\RequestQuote\Model\Source\Status;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * @api
 * Move approved quote items in active cart.
 */
class MoveInCart
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

    /**
     * @var MergeQuotes
     */
    private $mergeQuotes;

    public function __construct(
        QuoteRepositoryInterface $quoteRepository,
        CartRepositoryInterface $cartRepository,
        GetAmastyQuote $getAmastyQuote,
        MergeQuotes $mergeQuotes
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->cartRepository = $cartRepository;
        $this->getAmastyQuote = $getAmastyQuote;
        $this->mergeQuotes = $mergeQuotes;
    }

    /**
     * @throws NotApprovedQuoteException
     * @throws QuoteAlreadyInCartException
     * @throws NoSuchEntityException
     */
    public function execute(int $quoteId, int $cartId): void
    {
        $cart = $this->cartRepository->getActive($cartId);
        if ($this->getAmastyQuote->execute($cart)) {
            throw new QuoteAlreadyInCartException(__(
                'It is possible to process one Quote at a time.
                You have already added Quote in your cart. Please proceed to checkout.'
            ));
        }

        $quote = $this->quoteRepository->get($quoteId);
        if ($quote->getStatus() !== Status::APPROVED) {
            throw new NotApprovedQuoteException($quoteId);
        }

        $this->mergeQuotes->execute($cart, $quote);
        $this->cartRepository->save($cart);
    }
}
