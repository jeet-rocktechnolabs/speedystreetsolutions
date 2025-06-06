<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Model\Source;

class HideButton implements \Magento\Framework\Option\ArrayInterface
{
    public const SHOW = 0;
    public const HIDE = 1;
    public const REPLACE_WITH_NEW_ONE = 2;
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::SHOW,
                'label' => __('No')
            ],
            [
                'value' => self::HIDE,
                'label' => __('Yes')
            ],
            [
                'value' => self::REPLACE_WITH_NEW_ONE,
                'label' => __('Replace with custom button')
            ]
        ];

        return $options;
    }
}
