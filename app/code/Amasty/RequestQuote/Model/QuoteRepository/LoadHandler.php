<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\QuoteRepository;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Helper\Cart as CartHelper;
use Amasty\RequestQuote\Model\Quote;
use Magento\Quote\Api\Data\CartExtensionFactory;

class LoadHandler
{
    /**
     * @var CartExtensionFactory
     */
    private $cartExtensionFactory;

    /**
     * @var CartHelper
     */
    private $cartHelper;

    public function __construct(
        CartExtensionFactory $cartExtensionFactory,
        CartHelper $cartHelper
    ) {
        $this->cartExtensionFactory = $cartExtensionFactory;
        $this->cartHelper = $cartHelper;
    }

    /**
     * @param QuoteInterface|Quote $quote
     * @return QuoteInterface
     */
    public function load(QuoteInterface $quote)
    {
        $quote->setItems($quote->getAllVisibleItems());
        $cartExtension = $quote->getExtensionAttributes();
        if ($cartExtension === null) {
            $cartExtension = $this->cartExtensionFactory->create();
        }
        $quote->setExtensionAttributes($cartExtension);

        $quote->setQuoteCustomerNote($this->cartHelper->retrieveCustomerNote($quote->getRemarks()));

        return $quote;
    }
}
