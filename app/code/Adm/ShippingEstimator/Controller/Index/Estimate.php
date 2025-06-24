<?php

namespace Adm\ShippingEstimator\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Amasty\RequestQuote\Model\Quote\Session as QuoteSession;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Framework\Controller\Result\JsonFactory;
use Meetanshi\Shippingperitem\Model\Carrier\Shippingperitem;
use Magento\Quote\Model\Quote\Address\RateResult;
use Psr\Log\LoggerInterface;

class Estimate extends Action
{
    protected $checkoutSession;
    protected $quoteSession;
    protected $rateRequestFactory;
    protected $jsonFactory;
    protected $shippingPeritem;
    protected $logger;

    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        QuoteSession $quoteSession,
        RateRequest $rateRequestFactory,
        JsonFactory $jsonFactory,
        Shippingperitem $shippingPeritem,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->quoteSession = $quoteSession;
        $this->rateRequestFactory = $rateRequestFactory;
        $this->jsonFactory = $jsonFactory;
        $this->shippingPeritem = $shippingPeritem;
        $this->logger = $logger;
    }

    public function execute()
    {
        $resultJson = $this->jsonFactory->create();

        try {
            $addressLine = $this->getRequest()->getParam('address');
            $city = $this->getRequest()->getParam('city');
            $postcode = $this->getRequest()->getParam('postcode');
            $country = $this->getRequest()->getParam('country', 'GB'); // Default country code
            $region = $this->getRequest()->getParam('region', ''); // Default region code

            $quote = $this->quoteSession->getQuote();
            $address = $quote->getShippingAddress();
            $address->setCountryId($country);
            $address->setPostcode($postcode);
            $address->setRegion($region);
            $address->setStreet($addressLine);
            $address->setCity($city);
            $address->setCollectShippingRates(true);

            $baddress = $quote->getBillingAddress();
            $baddress->setCountryId($country);
            $baddress->setPostcode($postcode);
            $baddress->setRegion($region);
            $baddress->setStreet([$addressLine]);
            $baddress->setCity($city);

            $items = $quote->getAllItems();
            if (empty($items)) {
                throw new \Exception('No items found in the cart.');
            }

            $request = $this->rateRequestFactory;
            $request->setAllItems($items);
            $request->setDestCountryId($country);
            $request->setDestPostcode($postcode);
            $request->setDestRegionId($address->getRegionId()); // Assuming region_id is set
            $request->setPackageValue($quote->getGrandTotal());
            $request->setPackageQty($quote->getItemsQty());
            $request->setPackageWeight($quote->getWeight());
            $request->setFreeMethodWeight(0);
            $request->setStoreId($quote->getStoreId());
            $request->setWebsiteId($quote->getStore()->getWebsiteId());
            $request->setFreeShipping(false);
            $request->setBaseCurrency($quote->getBaseCurrencyCode());
            $request->setPackageCurrency($quote->getQuoteCurrencyCode());

            /** @var RateResult $rateResult */
            $rateResult = $this->shippingPeritem->collectRates($request);

            if (!$rateResult) {
                throw new \Exception('Unable to collect rates.');
            }

            $shippingRates = [];
            foreach ($rateResult->getAllRates() as $rate) {
                $shippingRates[] = [
                    'carrier' => $rate->getCarrier(),
                    'method' => $rate->getMethod(),
                    'price' => $rate->getPrice(),
                    'cost' => $rate->getCost(),
                    'carrier_title' => $rate->getCarrierTitle(),
                    'method_title' => $rate->getMethodTitle()
                ];
            }

            if (!empty($shippingRates)) {
                $shippingCost = $shippingRates[0]['price'];
                $shippingTax = $this->calculateShippingTax($shippingCost);
                //$this->logger->debug('Shipping Cost: ' . $shippingCost);

                // Use Magento's methods to set the shipping amounts
                $address->setShippingAmount($shippingCost);
                $address->setBaseShippingAmount($shippingCost);
                $address->setShippingTaxAmount($shippingTax); 
                $address->setBaseShippingTaxAmount($shippingTax);

                // Set the shipping method
                $address->setShippingMethod($shippingRates[0]['carrier'] . '_' . $shippingRates[0]['method']);
                $address->setShippingDescription($shippingRates[0]['carrier_title'] . ' - ' . $shippingRates[0]['method_title']);

                // Save the address to update the shipping cost in the quote_address table
                $address->save();
                $baddress->save();

                // Calculate the grand total with shipping cost
                $quote->setShippingAmount($shippingCost);
                $quote->setBaseShippingAmount($shippingCost);

                // Update quote totals
                $quote->setShippingAddress($address);
                $quote->setTotalsCollectedFlag(false);
                $quote->collectTotals();
                $quote->save();
                //$this->logger->debug('Shipping details saved.');
                $totalTax = $quote->getShippingAddress()->getTaxAmount();
                $grandTotal = $quote->getGrandTotal();
            } else {
                throw new \Exception('No shipping rates found.');
            }

            return $resultJson->setData([
                'success' => true,
                'rates' => $shippingRates,
                'totalTax' => $totalTax,
                'grandTotal' => $grandTotal
            ]);
        } catch (\Exception $e) {
            return $resultJson->setData(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function calculateShippingTax($shippingCost)
    {
        // Assuming a fixed tax rate for simplicity. Replace this with actual tax calculation logic.
        $taxRate = 0.2; // 20% VAT
        return $shippingCost * $taxRate;
    }
}
