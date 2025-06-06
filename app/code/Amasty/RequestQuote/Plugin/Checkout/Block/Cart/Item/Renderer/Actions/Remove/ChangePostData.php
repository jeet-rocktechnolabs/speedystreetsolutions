<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Plugin\Checkout\Block\Cart\Item\Renderer\Actions\Remove;

use Amasty\RequestQuote\Controller\Quote\Remove as RemoveController;
use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Remove as RemoveAction;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Url\EncoderInterface as UrlEncoder;
use Magento\Framework\UrlInterface as UrlBuilder;

class ChangePostData
{
    private const REMOVE_QUOTE_URL = 'amasty_quote/quote/remove';

    /**
     * @var UrlBuilder
     */
    private $urlBuilder;

    /**
     * @var UrlEncoder
     */
    private $urlEncoder;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(
        UrlBuilder $urlBuilder,
        UrlEncoder $urlEncoder,
        RequestInterface $request,
        Escaper $escaper,
        SerializerInterface $serializer
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->urlEncoder = $urlEncoder;
        $this->request = $request;
        $this->escaper = $escaper;
        $this->serializer = $serializer;
    }

    /**
     * Create custom data-post content for amasty quote item in cart.
     */
    public function aroundGetDeletePostJson(RemoveAction $subject, callable $proceed): string
    {
        $quoteItem = $subject->getItem();
        if (!$quoteItem->getOptionByCode('amasty_quote_price')) {
            return $proceed();
        }

        $data = [
            RemoveController::ITEM_ID_PARAM => $quoteItem->getId(),
            'confirmation' => true,
            'confirmationMessage' => sprintf(
                '%s<br>%s',
                $this->escaper->escapeHtml(
                    __('Are you sure you would like to remove this item from the shopping cart?')
                ),
                $this->escaper->escapeHtml(
                    __('This item is a part of the approved quote.
                    Removing it will remove all quote items from the cart.')
                )
            )
        ];
        if (!$this->request->isAjax()) {
            $data[ActionInterface::PARAM_NAME_URL_ENCODED] = $this->urlEncoder->encode(
                $this->urlBuilder->getCurrentUrl()
            );
        }

        return $this->serializer->serialize([
            'action' => $this->urlBuilder->getUrl(self::REMOVE_QUOTE_URL),
            'data' => $data
        ]);
    }
}
