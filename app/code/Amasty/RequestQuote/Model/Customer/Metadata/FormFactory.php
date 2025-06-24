<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Customer\Metadata;

use Magento\Customer\Model\Metadata\Form;

class FormFactory extends \Magento\Customer\Model\Metadata\FormFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var string
     */
    private $instanceName;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Amasty\RequestQuote\Model\Customer\Metadata\Form::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * Create Form
     *
     * @param string $entityType
     * @param string $formCode
     * @param array $attributeValues Key is attribute code.
     * @param bool $isAjax
     * @param bool $ignoreInvisible
     * @param array $filterAttributes
     * @return \Magento\Customer\Model\Metadata\Form
     */
    public function create(
        $entityType,
        $formCode,
        array $attributeValues = [],
        $isAjax = false,
        $ignoreInvisible = Form::IGNORE_INVISIBLE,
        $filterAttributes = []
    ) {
        $params = [
            'entityType' => $entityType,
            'formCode' => $formCode,
            'attributeValues' => $attributeValues,
            'ignoreInvisible' => $ignoreInvisible,
            'filterAttributes' => $filterAttributes,
            'isAjax' => $isAjax,
        ];
        return $this->objectManager->create($this->instanceName, $params);
    }
}
