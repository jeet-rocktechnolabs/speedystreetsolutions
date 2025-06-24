<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Model\Source;

class PriceMode implements \Magento\Framework\Option\ArrayInterface
{
    public const HIDE = 0;
    public const SHOW = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::HIDE,
                'label' => __('Hide')
            ],
            [
                'value' => self::SHOW,
                'label' => __('Show')
            ]
        ];

        return $options;
    }

    /**
     * Rewrite method for using in mass update attribute
     * @param $attribute
     * @return $this
     */
    public function setAttribute($attribute)
    {
        return $this;
    }

    /**
     * Retrieve Full Option values array
     *
     * @param bool $withEmpty       Add empty option to array
     * @param bool $defaultValues
     * @return array
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        $options = $this->toOptionArray();
        $options[] = [
            'value' => '',
            'label' => __('-- Default Config --')
        ];

        return $options;
    }
}
