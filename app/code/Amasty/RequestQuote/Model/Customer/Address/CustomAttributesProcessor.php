<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Customer\Address;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;

class CustomAttributesProcessor
{
    /**
     * @var AddressMetadataInterface
     */
    private $addressMetadata;

    /**
     * @var AttributeOptionManagementInterface
     */
    private $attributeOptionManager;

    public function __construct(
        AddressMetadataInterface $addressMetadata,
        AttributeOptionManagementInterface $attributeOptionManager
    ) {
        $this->addressMetadata = $addressMetadata;
        $this->attributeOptionManager = $attributeOptionManager;
    }

    /**
     * Set Labels to custom Attributes
     *
     * @param AttributeValue[] $customAttributes
     * @return array $customAttributes
     * @throws InputException
     * @throws StateException
     */
    private function setLabelsForAttributes(array $customAttributes): array
    {
        if (!empty($customAttributes)) {
            foreach ($customAttributes as $customAttributeCode => $customAttribute) {
                $attributeOptionLabels = $this->getAttributeLabels($customAttribute, $customAttributeCode);
                if (!empty($attributeOptionLabels)) {
                    $customAttributes[$customAttributeCode]['label'] = implode(', ', $attributeOptionLabels);
                }
            }
        }

        return $customAttributes;
    }
    /**
     * Get Labels by CustomAttribute and CustomAttributeCode
     *
     * @param array $customAttribute
     * @param string $customAttributeCode
     * @return array $attributeOptionLabels
     * @throws InputException
     * @throws StateException
     */
    private function getAttributeLabels(array $customAttribute, string $customAttributeCode) : array
    {
        $attributeOptionLabels = [];

        if (!empty($customAttribute['value'])) {
            $customAttributeValues = explode(',', $customAttribute['value']);
            $attributeOptions = $this->attributeOptionManager->getItems(
                \Magento\Customer\Model\Indexer\Address\AttributeProvider::ENTITY,
                $customAttributeCode
            );

            if (!empty($attributeOptions)) {
                foreach ($attributeOptions as $attributeOption) {
                    $attributeOptionValue = $attributeOption->getValue();
                    if (\in_array($attributeOptionValue, $customAttributeValues, false)) {
                        $attributeOptionLabels[] = $attributeOption->getLabel() ?? $attributeOptionValue;
                    }
                }
            }
        }

        return $attributeOptionLabels;
    }

    /**
     * Filter not visible on storefront custom attributes.
     *
     * @param array $attributes
     * @return array
     * @throws LocalizedException
     */
    public function filterNotVisibleAttributes(array $attributes): array
    {
        $attributesMetadata = $this->addressMetadata->getAllAttributesMetadata();
        foreach ($attributesMetadata as $attributeMetadata) {
            if (!$attributeMetadata->isVisible()) {
                unset($attributes[$attributeMetadata->getAttributeCode()]);
            }
        }

        return $this->setLabelsForAttributes($attributes);
    }
}
