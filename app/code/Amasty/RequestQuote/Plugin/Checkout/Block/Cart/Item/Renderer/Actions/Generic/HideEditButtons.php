<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Plugin\Checkout\Block\Cart\Item\Renderer\Actions\Generic;

use Amasty\RequestQuote\Plugin\Checkout\Block\Cart\Item\Renderer\Actions\Remove\ChangePostData;
use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic as NativeGeneric;
use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Remove as RemoveAction;

class HideEditButtons
{
    /**
     * Hide all edit buttons except remove button.
     * Remove button logic change here @see ChangePostData
     */
    public function afterFetchView(NativeGeneric $subject, string $html): string
    {
        if ($subject instanceof RemoveAction) {
            return $html;
        }

        if ($subject->getItem()->getOptionByCode('amasty_quote_price')) {
            $html = '';
        }

        return $html;
    }
}
