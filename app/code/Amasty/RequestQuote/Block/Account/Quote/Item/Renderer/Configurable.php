<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Account\Quote\Item\Renderer;

use Amasty\RequestQuote\Traits\Account\Quote\PriceRenderer;

class Configurable extends \Magento\ConfigurableProduct\Block\Cart\Item\Renderer\Configurable
{
    use PriceRenderer;
}
