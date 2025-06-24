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
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Psr\Log\LoggerInterface;

class RemoveFromMinicart implements HttpPostActionInterface
{
    private const ITEM_ID_PARAM = 'item_id';

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
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        RequestInterface $request,
        ResultFactory $resultFactory,
        Cart $cart,
        FormKeyValidator $formKeyValidator,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->cart = $cart;
        $this->formKeyValidator = $formKeyValidator;
        $this->logger = $logger;
    }

    /**
     * @return ResultJson
     */
    public function execute()
    {
        /** @var ResultJson $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        if (!$this->formKeyValidator->validate($this->request)) {
            $resultJson->setData([
                'success' => false,
                'error_message' => __('We can\'t remove the quote.')
            ]);
            return $resultJson;
        }

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
                    $error = __('We can\'t remove the quote.');
                    $this->logger->critical($e);
                }
            }
        }

        if (isset($error)) {
            $resultData = [
                'success' => false,
                'error_message' => $error
            ];
        } else {
            $resultData['success'] = true;
        }
        $resultJson->setData($resultData);

        return $resultJson;
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
