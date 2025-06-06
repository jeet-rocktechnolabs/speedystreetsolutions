<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Cart\Item\Renderer\Actions;

class Remove extends \Magento\Checkout\Block\Cart\Item\Renderer\Actions\Remove
{
    /**
     * @return string
     */
    public function getDeletePostJson()
    {
        /**
         * @TODO use own serializer
         */
        $deleteJson = json_decode($this->cartHelper->getDeletePostJson($this->getItem()), true);
        $deleteJson['action'] = $this->getUrl('amasty_quote/cart/delete');

        return json_encode($deleteJson);
    }
}
