<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Source;

class Status implements \Magento\Framework\Option\ArrayInterface
{
    public const CREATED = 0;
    public const PENDING = 1;
    public const APPROVED = 2;
    public const COMPLETE = 3;
    public const CANCELED = 4;
    public const EXPIRED = 5;
    public const HOLDED = 6;
    public const ADMIN_NEW = 7;
    public const ADMIN_CREATED = 8;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getOptionArray();
    }

    public function getOptionArray($excludeNew = false)
    {
        $options = [
            [
                'value' => self::PENDING,
                'label' => __('Pending')
            ],
            [
                'value' => self::APPROVED,
                'label' => __('Approved')
            ],
            [
                'value' => self::COMPLETE,
                'label' => __('Complete')
            ],
            [
                'value' => self::CANCELED,
                'label' => __('Canceled')
            ],
            [
                'value' => self::EXPIRED,
                'label' => __('Expired')
            ],
            [
                'value' => self::ADMIN_CREATED,
                'label' => __('Created from admin')
            ],
        ];

        if (!$excludeNew) {
            array_unshift($options, [
                'value' => self::CREATED,
                'label' => __('New')
            ]);
        }

        return $options;
    }

    /**
     * @param $status
     *
     * @return string
     */
    public function getStatusLabel($status)
    {
        $statusLabel = '';
        $options = $this->toOptionArray();
        foreach ($options as $option) {
            if ($option['value'] == $status) {
                $statusLabel = $option['label'];
                break;
            }
        }

        return $statusLabel;
    }

    /**
     * @return array
     */
    public function getVisibleOnFrontStatuses()
    {
        return [
            self::PENDING,
            self::APPROVED,
            self::COMPLETE,
            self::CANCELED,
            self::EXPIRED
        ];
    }
}
