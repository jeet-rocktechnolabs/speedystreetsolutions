<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Plugin\RequestQuote\Model\QuoteItemRepository;

use Amasty\RequestQuote\Api\Data\QuoteItemInterface;
use Amasty\RequestQuote\Model\QuoteItemRepository;
use Magento\Framework\App\RequestInterface;

class UpdateQuoteId
{
    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @see QuoteItemRepository::save
     */
    public function beforeSave(QuoteItemRepository $quoteItemRepository, QuoteItemInterface $quoteItem): void
    {
        $quoteId = (int)$this->request->getParam('quoteId');

        if ($quoteId && !$quoteItem->getQuoteId()) {
            $quoteItem->setQuoteId($quoteId);
        }
    }
}
