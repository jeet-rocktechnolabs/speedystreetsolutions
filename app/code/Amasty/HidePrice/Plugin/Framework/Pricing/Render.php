<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Plugin\Framework\Pricing;

use Amasty\HidePrice\Helper\Data;
use Amasty\HidePrice\Model\IsNeedRenderPrice;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\State;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render as PricingRender;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

class Render
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var State
     */
    private $state;

    /**
     * @var IsNeedRenderPrice
     */
    private $isNeedRenderPrice;

    public function __construct(
        Session $customerSession,
        StoreManagerInterface $storeManager,
        Registry $coreRegistry,
        ManagerInterface $eventManager,
        EncoderInterface $jsonEncoder,
        IsNeedRenderPrice $isNeedRenderPrice,
        Data $helper,
        State $state
    ) {
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->coreRegistry = $coreRegistry;
        $this->eventManager = $eventManager;
        $this->helper = $helper;
        $this->jsonEncoder = $jsonEncoder;
        $this->state = $state;
        $this->isNeedRenderPrice = $isNeedRenderPrice;
    }

    /**
     * @param PricingRender $subject
     * @param callable $proceed
     * @param $priceCode
     * @param SaleableInterface $saleableItem
     * @param array $arguments
     * @return string
     */
    public function aroundRender(
        PricingRender $subject,
        callable $proceed,
        $priceCode,
        SaleableInterface $saleableItem,
        array $arguments = []
    ) {
        if (!$this->isNeedRenderPrice->execute($saleableItem, $arguments)
            && $this->state->getAreaCode() != \Magento\Framework\App\Area::AREA_ADMINHTML
            && $price = $this->renderPrice($saleableItem, $priceCode, $arguments)
        ) {
            return $price;
        }

        if ($this->helper->getHideAddToCart() && $this->helper->isApplied($saleableItem)) {
            $this->saveDataToSession($saleableItem);
        }

        return $proceed($priceCode, $saleableItem, $arguments);
    }

    public function renderPrice(SaleableInterface $saleableItem, string $priceCode, array $arguments = []): ?string
    {
        $this->saveDataToSession($saleableItem);
        $additional = $this->generateJsHideOnCategory($saleableItem);

        if ($this->helper->getModuleConfig('information/hide_price')) {
            $priceHtml = $this->getNewPriceHtmlBox($saleableItem, $priceCode, $arguments);
            $result = $priceHtml . $additional;
        }

        return $result ?? null;
    }

    /**
     * @param PricingRender $subject
     * @param callable $proceed
     * @param AmountInterface $amount
     * @param PriceInterface $price
     * @param SaleableInterface $saleableItem
     * @param array $arguments
     * @return string
     */
    public function aroundRenderAmount(
        PricingRender $subject,
        callable $proceed,
        AmountInterface $amount,
        PriceInterface $price,
        SaleableInterface $saleableItem = null,
        array $arguments = []
    ) {
        if ($this->isNeedRenderPrice->execute($saleableItem, $arguments)) {
            // Show Price Box
            $result = $proceed($amount, $price, $saleableItem, $arguments);
            return $result;
        }

        return '';
    }

    private function getNewPriceHtmlBox($saleableItem, $priceCode, $arguments)
    {
        $html = '';

        /* get price replacement only for final price - others is hided*/
        $arguments['id_suffix'] = isset($arguments['id_suffix']) ? $arguments['id_suffix'] : '';
        if (in_array($priceCode, ['final_price', 'wishlist_configured_price']) && $arguments['id_suffix'] != 'copy-') {
            $html = $this->helper->getNewPriceHtmlBox($saleableItem);
        }

        return $html;
    }

    /**
     * @deprecated Hiding product button in another function
     * Js for for hiding product button on category page
     *
     * @param $saleableItem
     *
     * @return string
     * @internal param string $sku
     * @internal param int $id
     */
    private function generateJsHideOnCategory($saleableItem)
    {
        if ($saleableItem->getHidePriceObserved()) {
            return;
        }

        $id = $saleableItem->getId();
        $name = $saleableItem->getName();

        $productId = 'amhideprice-product-id-' . $id . '-' . random_int(1, 10000);
        // TODO - remove js code - button is replaced by observer
        $html = '<span ' . Data::HIDE_PRICE_DATA_ROLE . '  id="'
            . $productId . '" style="display: none !important;"></span>
             <script>
                require([
                    "jquery",
                     "Amasty_HidePrice/js/amhideprice"
                ], function ($, amhideprice) {
                    $( document ).ready(function() {
                        $("#' . $productId . '").amhideprice(' .
                            $this->jsonEncoder->encode([
                                'parent' => $this->helper->getModuleConfig('developer/parent'),
                                'button' => $this->helper->getModuleConfig('developer/addtocart'),
                                'html' => $this->helper->getNewAddToCartHtml(null, [
                                    'id' => $id,
                                    'name' => $name
                                ]),
                                'hide_compare' => $this->helper->getModuleConfig('information/hide_compare'),
                                'hide_wishlist' => $this->helper->getModuleConfig('information/hide_wishlist'),
                                'hide_addtocart' => $this->helper->getModuleConfig('information/hide_button')
                            ])
                        . ');
                    });
                });
            </script>';
        $saleableItem->setHidePriceObserved(true);

        return $html;
    }

    /**
     * @param $saleableItem
     */
    private function saveDataToSession(SaleableInterface $saleableItem)
    {
        $dataArray = $this->coreRegistry->registry('amasty_hideprice_data_array');
        if (!$dataArray) {
            $dataArray = [];
        }

        $dataArray[] = [
            'sku' => $saleableItem->getSku(),
            'id' => $saleableItem->getId(),
            'name' => $saleableItem->getName()
        ];

        $this->coreRegistry->unregister('amasty_hideprice_data_array');
        $this->coreRegistry->register('amasty_hideprice_data_array', $dataArray);
    }

    /**
     * @param string $html
     * @param $saleableItem
     * @return string
     */
    private function updatePriceHtml($html, $saleableItem)
    {
        $html .= $this->generateJsHideOnCategory($saleableItem);

        return $html;
    }
}
