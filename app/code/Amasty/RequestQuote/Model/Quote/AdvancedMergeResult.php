<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote;

use Amasty\RequestQuote\Api\Data\AdvancedMergeResultInterface;
use Magento\Framework\Phrase;

class AdvancedMergeResult implements AdvancedMergeResultInterface
{
    /**
     * @var bool
     */
    private $result;

    /**
     * @var Phrase
     */
    private $warnings;

    /**
     * @param bool $result
     * @param Phrase[] $warnings
     */
    public function __construct(bool $result, array $warnings = [])
    {
        $this->result = $result;
        $this->warnings = array_unique($warnings);
    }

    public function getResult(): bool
    {
        return $this->result;
    }

    /**
     * @return Phrase[]
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }
}
