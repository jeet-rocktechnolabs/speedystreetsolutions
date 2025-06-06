<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Adminhtml\System\Config;

use Amasty\RequestQuote\Model\Source\PdfVariables;
use Magento\Backend\Block\Template\Context;
use Amasty\RequestQuote\Block\Pdf\PdfTemplate as PdfBlock;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class PdfTemplate extends Field
{
    public const ROWS_NUMBER = 20;

    /**
     * @var PdfBlock
     */
    private $pdfTemplate;

    /**
     * @var PdfVariables
     */
    private $pdfVariables;

    public function __construct(
        Context $context,
        PdfBlock $pdfTemplate,
        PdfVariables $pdfVariables,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->pdfTemplate = $pdfTemplate;
        $this->pdfVariables = $pdfVariables;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        if (!$element->getValue()) {
            $element->setValue($this->pdfTemplate->toHtml());
        }
        $element->setTooltip($this->generateTooltip());
        $element->setRows(self::ROWS_NUMBER);

        return parent::render($element);
    }

    private function generateTooltip(): string
    {
        $comment = '';
        foreach ($this->pdfVariables->toOptionArray() as $item) {
            $comment .= sprintf('%s - %s<br>', $item['label'], $item['value']);
        }

        return $comment;
    }
}
