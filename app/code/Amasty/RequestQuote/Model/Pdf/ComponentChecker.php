<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Pdf;

use Magento\Framework\Phrase;

class ComponentChecker
{
    public function isComponentsExist(): bool
    {
        try {
            $classExists = class_exists(\Dompdf\Dompdf::class);
        } catch (\Exception $e) {
            $classExists = false;
        }

        return $classExists;
    }

    public function getComponentsErrorMessage(): Phrase
    {
        return __(
            'To use PDF functionality, please install the library dompdf/dompdf.
            To do this, run the command "composer require dompdf/dompdf" in the main site folder.'
        );
    }
}
