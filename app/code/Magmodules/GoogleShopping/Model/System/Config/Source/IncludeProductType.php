<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\GoogleShopping\Model\System\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class IncludeProductType implements OptionSourceInterface
{
    public const INCLUDE_PRODUCT_TYPE_NO = 0;
    public const INCLUDE_PRODUCT_TYPE_YES = 1;
    public const INCLUDE_PRODUCT_TYPE_ATTRIBUTE = 2;
    public const INCLUDE_PRODUCT_TYPE_YES_LAST_CATEGORY = 3;

    /**
     * Options array
     *
     * @var array
     */
    public $options = null;

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        if (!$this->options) {
            $this->options = [
                [
                    'value' => self::INCLUDE_PRODUCT_TYPE_NO,
                    'label' => __('No')
                ],
                [
                    'value' => self::INCLUDE_PRODUCT_TYPE_YES,
                    'label' => __('Yes')
                ],
                [
                    'value' => self::INCLUDE_PRODUCT_TYPE_YES_LAST_CATEGORY,
                    'label' => __('Yes, but only last category')
                ],
                [
                    'value' => self::INCLUDE_PRODUCT_TYPE_ATTRIBUTE,
                    'label' => __('Manually')
                ]
            ];
        }
        return $this->options;
    }
}
