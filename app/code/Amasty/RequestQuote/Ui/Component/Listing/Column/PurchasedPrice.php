<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class PurchasedPrice extends Column
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceFormatter;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        PriceCurrencyInterface $priceFormatter,
        array $components = [],
        array $data = []
    ) {
        $this->priceFormatter = $priceFormatter;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $currencyCode = isset($item['quote_currency_code']) ? $item['quote_currency_code'] : null;
                $item[$this->getData('name')] =
                    $this->priceFormatter->format(
                        $item[$this->getData('name')],
                        false,
                        PriceCurrencyInterface::DEFAULT_PRECISION,
                        null,
                        $currencyCode
                    );
            }
        }

        return $dataSource;
    }
}
