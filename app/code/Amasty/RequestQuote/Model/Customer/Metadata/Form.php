<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Customer\Metadata;

use \Amasty\RequestQuote\Plugin\CustomerCustomAttributes\Helper\DataPlugin;
use Magento\Framework\Exception\LocalizedException;

class Form extends \Magento\Customer\Model\Metadata\Form
{
    /**
     * @return array|\Magento\Customer\Api\Data\AttributeMetadataInterface[]
     */
    public function getAllowedAttributes()
    {
        $attributes = [];
        foreach (parent::getAllowedAttributes() as $allowedAttribute) {
            if ($allowedAttribute->isRequired()) {
                $attributes[$allowedAttribute->getAttributeCode()] = $allowedAttribute;
            }
        }
        try {
            $attributesFromQuoteForm = $this->_customerMetadataService->getAttributes(DataPlugin::QUOTE_FORM);
            foreach ($attributesFromQuoteForm as $allowedAttribute) {
                if (!isset($attributes[$allowedAttribute->getAttributeCode()])) {
                    $attributes[] = $allowedAttribute;
                }
            }
        } catch (LocalizedException $e) {
            null;
        }

        return array_values($attributes);
    }
}
