<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PdfVariables implements OptionSourceInterface
{
    public const USERNAME = 'username';
    public const CUSTOMER_STREET = 'customer_street';
    public const CUSTOMER_CITY = 'customer_city';
    public const CUSTOMER_REGION = 'customer_region';
    public const CUSTOMER_POSTCODE = 'customer_postcode';
    public const CUSTOMER_COUNTRY = 'customer_country';
    public const CUSTOMER_TELEPHONE = 'customer_telephone';
    public const QUOTE_NUMBER = 'quote_number';
    public const QUOTE_STATUS = 'quote_status';
    public const QUOTE_DATE = 'quote_date';
    public const QUOTE_EXPIRY_DATE = 'quote_expiry_date';
    public const HAS_SHIPPING_INFO = 'has_shipping_info';
    public const SHIPPING_STREET = 'shipping_street';
    public const SHIPPING_CITY = 'shipping_city';
    public const SHIPPING_REGION = 'shipping_region';
    public const SHIPPING_POSTCODE = 'shipping_postcode';
    public const SHIPPING_COUNTRY = 'shipping_country';
    public const SHIPPING_TELEPHONE = 'shipping_telephone';
    public const SHIPPING_METHOD = 'shipping_method';
    public const PRODUCT_GRID = 'product_grid';
    public const STORE_PHONE = 'store_phone';
    public const QUOTE_SUBMITTED_DATE = 'quote_submitted_date';

    /**
     * @return array|array[]
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::USERNAME,
                'label' => __('Username')
            ],
            [
                'value' => self::CUSTOMER_STREET,
                'label' => __('Customer Street')
            ],
            [
                'value' => self::CUSTOMER_REGION,
                'label' => __('Customer Region')
            ],
            [
                'value' => self::CUSTOMER_POSTCODE,
                'label' => __('Customer Postcode')
            ],
            [
                'value' => self::CUSTOMER_COUNTRY,
                'label' => __('Customer Country')
            ],
            [
                'value' => self::CUSTOMER_TELEPHONE,
                'label' => __('Customer Telephone')
            ],
            [
                'value' => self::QUOTE_NUMBER,
                'label' => __('Quote Number')
            ],
            [
                'value' => self::QUOTE_STATUS,
                'label' => __('Quote Status')
            ],
            [
                'value' => self::QUOTE_DATE,
                'label' => __('Quote Date')
            ],
            [
                'value' => self::QUOTE_EXPIRY_DATE,
                'label' => __('Quote Expiry Date')
            ],
            [
                'value' => self::HAS_SHIPPING_INFO,
                'label' => __('Has Shipping Info')
            ],
            [
                'value' => self::SHIPPING_STREET,
                'label' => __('Shipping Street')
            ],
            [
                'value' => self::SHIPPING_CITY,
                'label' => __('Shipping City')
            ],
            [
                'value' => self::SHIPPING_REGION,
                'label' => __('Shipping Region')
            ],
            [
                'value' => self::SHIPPING_POSTCODE,
                'label' => __('Shipping Postcode')
            ],
            [
                'value' => self::SHIPPING_COUNTRY,
                'label' => __('Shipping Country')
            ],
            [
                'value' => self::SHIPPING_TELEPHONE,
                'label' => __('Shipping Telephone')
            ],
            [
                'value' => self::SHIPPING_METHOD,
                'label' => __('Shipping Method')
            ],
            [
                'value' => self::PRODUCT_GRID,
                'label' => __('Product Grid')
            ],
            [
                'value' => self::STORE_PHONE,
                'label' => __('Store Phone')
            ],
            [
                'value' => self::QUOTE_SUBMITTED_DATE,
                'label' => __('Quote Submitted Date')
            ]
        ];
    }
}
