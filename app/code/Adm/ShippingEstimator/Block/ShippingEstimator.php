<?php
namespace Adm\ShippingEstimator\Block;

use Magento\Framework\View\Element\Template;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Meetanshi\Shippingperitem\Model\Carrier\Shippingperitem;

class ShippingEstimator extends Template
{
    protected $shippingperitem;

    public function __construct(
        Template\Context $context,
        Shippingperitem $shippingperitem,
        array $data = []
    ) {
        $this->shippingperitem = $shippingperitem;
        parent::__construct($context, $data);
    }

    public function getEstimateUrl()
    {
        return $this->getUrl('shipping_estimator/index/index');
    }

    public function getShippingCost($postcode)
    {
        $request = new RateRequest();
        $request->setDestPostcode($postcode);

        $result = $this->shippingperitem->collectRates($request);
        $rates = $result->getAllRates();

        if (!empty($rates)) {
            foreach ($rates as $rate) {
                return $rate->getPrice();
            }
        }
        return false;
    }
}
