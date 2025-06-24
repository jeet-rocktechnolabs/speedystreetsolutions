<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Pdf;

use Amasty\RequestQuote\Helper\Data;
use Amasty\RequestQuote\Model\Registry;
use Amasty\RequestQuote\Model\RegistryConstants;
use Magento\Framework\DataObject;

class HtmlGenerator
{
    /**
     * @var Data
     */
    private $data;

    /**
     * @var PdfInformation
     */
    private $pdfInformation;

    /**
     * @var Template
     */
    private $template;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        Data $data,
        PdfInformation $pdfInformation,
        Template $template,
        Registry $registry
    ) {
        $this->data = $data;
        $this->pdfInformation = $pdfInformation;
        $this->template = $template;
        $this->registry = $registry;
    }

    public function getHtmlByQuote(): string
    {
        $template = $this->data->getTemplateContent();

        $transportObject = new DataObject($this->pdfInformation->getQuoteDataForPdf());
        $quote = $this->registry->registry(RegistryConstants::AMASTY_QUOTE);
        $template = $this->template->setTemplateText($template)
            ->setVars($transportObject->getData())
            ->setOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $quote->getStoreId()
                ]
            );

        return $template->processTemplate();
    }
}
