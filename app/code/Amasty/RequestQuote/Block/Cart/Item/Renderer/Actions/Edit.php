<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Cart\Item\Renderer\Actions;

use Amasty\RequestQuote\Model\UrlResolver;
use Magento\Framework\View\Element\Template\Context;

class Edit extends \Magento\Checkout\Block\Cart\Item\Renderer\Actions\Edit
{
    /**
     * @var UrlResolver
     */
    private $urlResolver;

    public function __construct(Context $context, UrlResolver $urlResolver, array $data = [])
    {
        parent::__construct($context, $data);
        $this->urlResolver = $urlResolver;
    }

    /**
     * @return string
     */
    public function getConfigureUrl()
    {
        $item = $this->getItem();

        return $this->urlResolver->getConfigureUrl(
            (int) $item->getId(),
            (int) $item->getProduct()->getId()
        );
    }
}
