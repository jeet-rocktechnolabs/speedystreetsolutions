<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class Yesnocustom implements ArrayInterface
{
    public const NO = 0;
    public const INSTANTLY = 2;
    public const CUSTOM = 1;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::CUSTOM, 'label' => __('Yes(customize)')],
            ['value' => self::INSTANTLY, 'label' => __('Yes(instantly)')],
            ['value' => self::NO, 'label' => __('No')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::NO => __('No'),
            self::INSTANTLY => __('Yes(instantly)'),
            self::CUSTOM => __('Yes(customize)')
        ];
    }
}
