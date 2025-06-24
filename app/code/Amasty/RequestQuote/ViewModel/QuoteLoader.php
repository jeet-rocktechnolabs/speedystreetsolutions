<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\ViewModel;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class QuoteLoader implements ArgumentInterface
{
    /**
     * @var QuoteRepositoryInterface
     */
    private $quoteRepository;

    public function __construct(QuoteRepositoryInterface $quoteRepository)
    {
        $this->quoteRepository = $quoteRepository;
    }

    public function load(int $quoteId): QuoteInterface
    {
        return $this->quoteRepository->get($quoteId, ['*']);
    }
}
