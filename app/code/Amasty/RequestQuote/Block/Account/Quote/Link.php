<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Account\Quote;

use Amasty\RequestQuote\Model\UrlResolver;
use Magento\Customer\Block\Account\SortLinkInterface;
use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\View\Element\Html\Link\Current;
use Magento\Framework\View\Element\Template\Context;

/**
 * Customer account "My Quotes" link data.
 *
 * Route part of the link can be customized via system configuration of the module.
 */
class Link extends Current implements SortLinkInterface
{
    /**
     * @var UrlResolver
     */
    private $urlResolver;

    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        UrlResolver $urlResolver,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->urlResolver = $urlResolver;
    }

    /**
     * Get href URL
     *
     * @return string
     */
    public function getHref()
    {
        return $this->urlResolver->getAccountUrl();
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return parent::getLabel() ?? __('My Quotes')->render();
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        if ($this->hasData(self::SORT_ORDER)) {
            return $this->getData(self::SORT_ORDER);
        }

        return 230;
    }

    public function getPath(): string
    {
        return 'amasty_quote/account/index';
    }
}
