<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Source\Email;

use UnexpectedValueException;

class AbstractTemplate extends \Magento\Framework\DataObject implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Email\Model\Template\Config
     */
    private $emailConfig;

    /**
     * @var \Magento\Email\Model\ResourceModel\Template\CollectionFactory
     */
    private $templatesFactory;

    /**
     * @var string
     */
    private $origTemplateCode;

    public function __construct(
        \Magento\Email\Model\ResourceModel\Template\CollectionFactory $templatesFactory,
        \Magento\Email\Model\Template\Config $emailConfig,
        $origTemplateCode = '',
        array $data = []
    ) {
        parent::__construct($data);
        $this->templatesFactory = $templatesFactory;
        $this->emailConfig = $emailConfig;
        $this->origTemplateCode = $origTemplateCode;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        /** @var $collection \Magento\Email\Model\ResourceModel\Template\Collection */
        $collection = $this->templatesFactory->create()
            ->addFieldToFilter('orig_template_code', ['eq' => $this->origTemplateCode])
            ->load();

        $options = $collection->toOptionArray();
        array_unshift($options, $this->getDefaultTemplate());

        return $options;
    }

    /**
     * @return array
     */
    private function getDefaultTemplate()
    {
        $templateId = str_replace('/', '_', $this->getPath());

        try {
            $templateLabel = $this->emailConfig->getTemplateLabel($templateId);
        } catch (UnexpectedValueException $e) {
            $templateLabel = $this->emailConfig->getTemplateLabel($this->origTemplateCode);
            $templateId = $this->origTemplateCode;
        }
        $templateLabel = __('%1 (Default)', $templateLabel);

        return ['value' => $templateId, 'label' => $templateLabel];
    }
}
