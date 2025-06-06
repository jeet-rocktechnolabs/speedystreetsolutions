<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Pdf;

use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Amasty\RequestQuote\Model\Quote;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;

class PdfProvider
{
    /**
     * @var PdfFactory
     */
    private $pdfFactory;

    /**
     * @var HtmlGenerator
     */
    private $htmlGenerator;

    /**
     * @var RawFactory
     */
    private $rawFactory;

    public function __construct(
        PdfFactory $pdfFactory,
        HtmlGenerator $htmlGenerator,
        RawFactory $rawFactory
    ) {
        $this->pdfFactory = $pdfFactory;
        $this->htmlGenerator = $htmlGenerator;
        $this->rawFactory = $rawFactory;
    }

    public function getRawPdf(Quote $quote): Raw
    {
        $rawPdf = $this->generatePdfText();

        $fileName = 'quote_' . $quote->getIncrementId() . '.pdf';
        $raw = $this->rawFactory->create();
        $raw->setHeader('Content-type', "application/x-pdf");
        $raw->setHeader('Content-Security-Policy', "script-src 'none'");
        $raw->setHeader('Content-Disposition', "inline; filename={$fileName}");
        $raw->setContents($rawPdf);

        return $raw;
    }

    public function generatePdfText(): ?string
    {
        /** @var \Amasty\RequestQuote\Model\Pdf\Pdf $pdf */
        $pdf = $this->pdfFactory->create();
        $pdf->setHtml($this->htmlGenerator->getHtmlByQuote());

        return $pdf->render();
    }
}
