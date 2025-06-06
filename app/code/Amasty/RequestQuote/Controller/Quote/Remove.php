<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Controller\Quote;

use Exception;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Psr\Log\LoggerInterface;

class Remove implements HttpPostActionInterface
{
    public const ITEM_ID_PARAM = 'id';

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var MessageManager
     */
    private $messageManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        RequestInterface $request,
        ResultFactory $resultFactory,
        Cart $cart,
        MessageManager $messageManager,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->cart = $cart;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($itemId = $this->getItemId()) {
            $quote = $this->cart->getQuote();
            if (($quoteItem = $quote->getItemById($itemId))
                && $this->isAmastyQuoteItem($quoteItem)
            ) {
                try {
                    foreach ($quote->getAllVisibleItems() as $quoteItem) {
                        if ($this->isAmastyQuoteItem($quoteItem)) {
                            $quoteItem->removeOption('amasty_quote_price');
                            $quote->deleteItem($quoteItem);
                        }
                    }

                    $quote->setTotalsCollectedFlag(false);
                    $this->cart->save();
                } catch (Exception $e) {
                    $this->messageManager->addErrorMessage(__('We can\'t remove the quote.'));
                    $this->logger->critical($e);
                }
            }
        }

        $resultRedirect->setRefererUrl();
        return $resultRedirect;
    }

    private function getItemId(): int
    {
        return (int) $this->request->getParam(self::ITEM_ID_PARAM);
    }

    private function isAmastyQuoteItem(QuoteItem $quoteItem): bool
    {
        return (bool) $quoteItem->getOptionByCode('amasty_quote_price');
    }
}
