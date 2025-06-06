<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Model\Source;

class Status implements \Magento\Framework\Option\ArrayInterface
{
    public const PENDING = 0;
    public const VIEWED = 1;
    public const ANSWERED = 2;

    /**
     * @var array
     */
    protected $options;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [
                [
                    'value' => self::PENDING,
                    'label' => __('Pending')
                ],
                [
                    'value' => self::VIEWED,
                    'label' => __('Viewed')
                ],
                [
                    'value' => self::ANSWERED,
                    'label' => __('Answered')
                ]
            ];
        }

        return $this->options;
    }

    public function getOptionByValue($value)
    {
        $options = $this->toOptionArray();
        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }

        return '';
    }
}
