<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote;

class TotalsCollectorList extends \Magento\Quote\Model\Quote\TotalsCollectorList
{
    /**
     * @var array
     */
    private $availableCollectors = [
        'subtotal',
        'tax_subtotal',
        'tax',
        'weee',
        'weee_tax',
        'grand_total',
        'shipping',
        'tax_shipping'
    ];

    /**
     * @inheritdoc
     */
    public function getCollectors($storeId)
    {
        $collectors = parent::getCollectors($storeId);
        foreach ($collectors as $key => $collector) {
            if (!in_array($key, $this->availableCollectors)) {
                unset($collectors[$key]);
            }
        }

        return $collectors;
    }
}
