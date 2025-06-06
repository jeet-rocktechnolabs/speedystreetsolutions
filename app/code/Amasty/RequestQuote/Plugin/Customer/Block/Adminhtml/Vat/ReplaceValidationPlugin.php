<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Plugin\Customer\Block\Adminhtml\Vat;

use Magento\Backend\Block\Widget\Button;
use Magento\Customer\Block\Adminhtml\Sales\Order\Address\Form\Renderer\Vat;

class ReplaceValidationPlugin
{
    /**
     * @param Vat $subject
     * @param Button $result
     * @return Button
     *
     * @see Vat
     */
    public function afterGetValidateButton(Vat $subject, Button $result): Button
    {
        if ($subject->getData('amquote_block')) {
            $onClick = str_replace('order.validateVat', 'quote.validateVat', $result->getData('onclick'));
            $result->setData('onclick', $onClick);
        }

        return $result;
    }
}
