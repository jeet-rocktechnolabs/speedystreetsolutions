<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Api\Data;

interface AdvancedMergeResultInterface
{
    /**
     * @return bool
     */
    public function getResult(): bool;

    /**
     * @return string[]
     */
    public function getWarnings(): array;
}
