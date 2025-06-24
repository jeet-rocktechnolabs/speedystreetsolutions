<?php

namespace Meetanshi\Shippingperitem\Model\Carrier;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;
use Magento\Checkout\Model\Session;

/**
 * Class Shippingperitem
 * @package Meetanshi\Shippingperitem\Model\Carrier
 */
class Shippingperitem extends AbstractCarrier implements CarrierInterface
{
    /**
     *
     */
    const ATTRIBUTE_CODE = 'shipping_peritem';
    /**
     * @var string
     */
    protected $_code = 'shippingperitem';
    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;
    /**
     * @var ErrorFactory
     */
    protected $rateErrorFactory;
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var _logger
     */
    protected $_logger;
    /**
     * @var ResultFactory
     */
    private $rateResultFactory;
    /**
     * @var MethodFactory
     */
    private $rateMethodFactory;
    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;
    /**
     * @var State
     */
    private $state;

    /**
     * Shippingperitem constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param CollectionFactory $productCollectionFactory
     * @param QuoteFactory $quoteFactory
     * @param Session $session
     * @param State $state
     * @param array $data
     */
    public function __construct(ScopeConfigInterface $scopeConfig, ErrorFactory $rateErrorFactory, LoggerInterface $logger, ResultFactory $rateResultFactory, MethodFactory $rateMethodFactory, CollectionFactory $productCollectionFactory, QuoteFactory $quoteFactory, Session $session, State $state, array $data = [])
    {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->state = $state;
        $this->quoteFactory = $quoteFactory;
        $this->rateErrorFactory = $rateErrorFactory;
        $this->session = $session;
        $this->_logger = $logger;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @param RateRequest $request
     * @return bool|\Magento\Framework\DataObject|\Magento\Quote\Model\Quote\Address\RateResult\Error|\Magento\Shipping\Model\Rate\Result|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function collectRates(RateRequest $request)
    {
        $result = $this->rateResultFactory->create();

        if (!$this->getConfigFlag('active')) {
            return false;
        }

        if ($this->getConfigFlag('for_admin')) {
            if ($this->state->getAreaCode() != 'adminhtml') {
                return false;
            }
        }

        $quoteId = false;
        foreach ($request->getAllItems() as $item) {
            $quoteId = $item->getQuoteId();
            break;
        }

        if (empty($quoteId)){
            $quoteId = $this->session->getQuoteId();
        }
        $quote = $this->getCurrentQuote($quoteId);
        $totals = $quote->getTotals();

        $min_order_amount = $this->getConfigData('min_order_amount');
        $max_order_amount = $this->getConfigData('max_order_amount');

        $yellowzonepcode = explode(",", $this->getConfigData('yellowzonepcode'));
        $orangezonepcode = explode(",", $this->getConfigData('orangezonepcode'));
        $pinkzonepcode = explode(",", $this->getConfigData('pinkzonepcode'));
        $bluezonepcode = explode(",", $this->getConfigData('bluezonepcode'));
        $greenzonepcode = explode(",", $this->getConfigData('greenzonepcode'));
        $clondonzonepcode = explode(",", $this->getConfigData('clondonzonepcode'));
        $purplezonepcode = explode(",", $this->getConfigData('purplezonepcode'));
        $redzonepcode = explode(",", $this->getConfigData('redzonepcode'));
        $shipping_cost_shelter_extra = $this->getConfigData('shipping_cost_shelter_extra');

        /*$shipping_cost_yellow_0 = $this->getConfigData('shipping_cost_yellow_0');
        $shipping_cost_yellow_1 = $this->getConfigData('shipping_cost_yellow_1');
        $shipping_cost_yellow_2 = $this->getConfigData('shipping_cost_yellow_2');
        $shipping_cost_orange_0 = $this->getConfigData('shipping_cost_orange_0');
        $shipping_cost_orange_1 = $this->getConfigData('shipping_cost_orange_1');
        $shipping_cost_orange_2 = $this->getConfigData('shipping_cost_orange_2');
        $shipping_cost_pink_0 = $this->getConfigData('shipping_cost_pink_0');
        $shipping_cost_pink_1 = $this->getConfigData('shipping_cost_pink_1');
        $shipping_cost_pink_2 = $this->getConfigData('shipping_cost_pink_2');
        $shipping_cost_blue_0 = $this->getConfigData('shipping_cost_blue_0');
        $shipping_cost_blue_1 = $this->getConfigData('shipping_cost_blue_1');
        $shipping_cost_blue_2 = $this->getConfigData('shipping_cost_blue_2');
        $shipping_cost_green_0 = $this->getConfigData('shipping_cost_green_0');
        $shipping_cost_green_1 = $this->getConfigData('shipping_cost_green_1');
        $shipping_cost_green_2 = $this->getConfigData('shipping_cost_green_2');
        $shipping_cost_clondon_0 = $this->getConfigData('shipping_cost_clondon_0');
        $shipping_cost_clondon_1 = $this->getConfigData('shipping_cost_clondon_1');
        $shipping_cost_clondon_2 = $this->getConfigData('shipping_cost_clondon_2');
        $shipping_cost_purple_0 = $this->getConfigData('shipping_cost_purple_0');
        $shipping_cost_purple_1 = $this->getConfigData('shipping_cost_purple_1');
        $shipping_cost_purple_2 = $this->getConfigData('shipping_cost_purple_2');
        $shipping_cost_red_0 = $this->getConfigData('shipping_cost_red_0');
        $shipping_cost_red_1 = $this->getConfigData('shipping_cost_red_1');
        $shipping_cost_red_2 = $this->getConfigData('shipping_cost_red_2');*/

        if ((!empty($min_order_amount) && $min_order_amount > $totals['subtotal']->getValue()) || (!empty($max_order_amount) && $max_order_amount < $totals['subtotal']->getValue())) {
            $error = $this->rateErrorFactory->create(
                [
                    'data' => [
                        'carrier' => $this->_code,
                        'carrier_title' => $this->getConfigData('title'),
                        'error_message' => $this->getConfigData('specificerrmsg'),
                    ],
                    'html' => [$this->getConfigData('specificerrmsg')]
                ]
            );
            $result->append($error);
            return $error;
        }

        $method = $this->rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));

        $attribute = self::ATTRIBUTE_CODE;

        $product_ids = [];
        $product_ship_cost = [];
        $product_ship_option = [];
        $pallet_shipping_only = [];
        $pallet_shipping_qty = [];
        $items_per_pallet = [];
        
        //$id_str='id';
        foreach ($request->getAllItems() as $item) {
            if ($item->getParentItem() || $item->getProduct()->isVirtual()) {
                continue;
            }
            $pobjectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $parent_product = $pobjectManager->get('Magento\Catalog\Model\Product')->load($item->getProduct()->getId());
            $parent_ship_cost = $parent_product->getShippingPeritem();
            $product_ship_option[$item->getProduct()->getId()] = $parent_product->getAttributeText('shippingoption');
            $pallet_shipping_only[$item->getProduct()->getId()] = $parent_product->getData('pallet_shipping_only');
            if($parent_product->getData('pallet_qty')){
                $pallet_shipping_qty[$item->getProduct()->getId()] = $parent_product->getData('pallet_qty');
            } else {
            $pallet_shipping_qty[$item->getProduct()->getId()] = 0;
            }
            if($parent_product->getData('items_per_pallet')){
                $items_per_pallet[$item->getProduct()->getId()] = $parent_product->getData('items_per_pallet');
            } else {
            $items_per_pallet[$item->getProduct()->getId()] = 0;
            }
            

            if ($item->getHasChildren()) {
                foreach ($item->getChildren() as $children) {
                    if ($children->getFreeShipping() || $children->getProduct()->isVirtual()) {
                        continue;
                    }
                    $id = $children->getProduct()->getId();
                    $product_ids[$id] = $item->getQty();
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $child_product = $objectManager->get('Magento\Catalog\Model\Product')->load($id);
                    $child_ship_cost = $child_product->getShippingPeritem();
                    $product_ship_option[$id] = $child_product->getAttributeText('shippingoption');
                    $pallet_shipping_only[$id] = $child_product->getData('pallet_shipping_only');
                    if($child_product->getData('pallet_qty')){
                        $pallet_shipping_qty[$id] = $child_product->getData('pallet_qty');
                    } else {
                        $pallet_shipping_qty[$id] = 0;
                    }
                    if($child_product->getData('items_per_pallet')){
                        $items_per_pallet[$id] = $child_product->getData('items_per_pallet');
                    } else {
                        $items_per_pallet[$id] = 0;
                    }

                    if($child_product->getAttributeText('shippingoption')!="Free" && $child_ship_cost>0){
                        $product_ship_cost[$id] = $child_product->getShippingPeritem();
                        //$this->_logger->debug("Shof 1");
                    } else if($child_product->getAttributeText('shippingoption')=="Free" && $child_ship_cost==0) {
                        $product_ship_cost[$id] = $child_product->getShippingPeritem();
                        //$this->_logger->debug("Shof 12");
                    } else {
                        $product_ship_cost[$id] = $parent_ship_cost;
                        //$this->_logger->debug("Shof 13");
                    }

                }
            } elseif (!$item->getFreeShipping()) {
                $id = $item->getProduct()->getId();
                $product_ids[$id] = $item->getQty();
                $product_ship_cost[$id] = $parent_ship_cost;
                //$this->_logger->debug("Shof 3");
            }
        }

        //$this->_logger->debug(print_r($product_ids,true));
        //$this->_logger->debug(print_r($product_ship_cost,true));
        ///$this->_logger->debug(print_r($product_ship_option,true));
        //$this->_logger->debug(print_r($pallet_shipping_only,true));
//$this->_logger->debug(print_r($pallet_shipping_qty,true));
        $postcode='';
        $p_code_part ='';
        $ship_zone='';

        if($request->getDestPostcode()){
            $postcode = $request->getDestPostcode();
        } else if($this->session->getQuote()->getShippingAddress()->getPostcode()) {
            $postcode = $this->session->getQuote()->getShippingAddress()->getPostcode();
        }
        ///$this->_logger->debug('Post Code:'.$postcode);
        if($postcode!=''){
            $p_code_length = strlen($postcode);
            $p_code_length = ($p_code_length - 2*$p_code_length)+2;
            $p_code_part = substr($postcode, 0, $p_code_length);
            $p_code_part = preg_replace('/[0-9]+/', '', $p_code_part);
            $p_code_part = strtoupper($p_code_part);
        }

        //$this->_logger->debug('p_code_part:'.$p_code_part);
        
        if (in_array($p_code_part , $yellowzonepcode)) {
            $ship_zone='yellow';
        } else if (in_array($p_code_part , $orangezonepcode)) {
            $ship_zone='orange';
        } else if (in_array($p_code_part , $pinkzonepcode)) {
            $ship_zone='pink';
        } else if (in_array($p_code_part , $bluezonepcode)) {
            $ship_zone='blue';
        } else if (in_array($p_code_part , $greenzonepcode)) {
            $ship_zone='green';
        } else if (in_array($p_code_part , $clondonzonepcode)) {
            $ship_zone='clondon';
        } else if (in_array($p_code_part , $purplezonepcode)) {
            $ship_zone='purple';
        } else if (in_array($p_code_part , $redzonepcode)) {
            $ship_zone='red';
        }
        
        

        /*$collection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('entity_id', ['in' => array_keys($product_ids)]);*/
            //$this->_logger->debug('Ship Zone:'.$ship_zone);
        if($ship_zone!=''){    

        $hasRateFound = false;

        $price = (float)$this->getConfigData('handling_fee');
        $rates = [];
        $p_qty_0=0;
        $p_qty_1=0;
        $p_qty_2=0;
        $pallet_qty_0=0;
        $pallet_qty_1=0;
        $pallet_qty_2=0;

        $p_pallet_qty_0=0;
        $p_pallet_qty_1=0;
        $p_pallet_qty_2=0;
        $is_free_shipping=false;
        $is_pallet0_shipping=false;
        $is_pallet1_shipping=false;
        $is_pallet2_shipping=false;
        $is_pallet1_shipping_only=false;
        $is_pallet2_shipping_only=false;
        foreach ($product_ids as $key=>$val) {
            $pallet_q =0;
            if($items_per_pallet[$key]>0){
                if($val%$items_per_pallet[$key]==0){
                $pallet_q = $val/$items_per_pallet[$key];
                } else {
                    $pallet_q = ceil($val/$items_per_pallet[$key]);
                }
            }
            if(($product_ship_option[$key]=="Pallet" || $product_ship_option[$key]=="Parcel") && $product_ship_cost[$key]>0 && $pallet_q > 0){
                $rate = $product_ship_cost[$key];
                $qty = $pallet_q;
                $rates[] = (1.0 * $rate * $qty);
            } else if($product_ship_option[$key]=="Pallet"){
                $pallet_qty_0+=$val;
                if($pallet_q>$pallet_shipping_qty[$key]){
                    $p_pallet_qty_0+=$pallet_q;
                } else {
                    $p_pallet_qty_0+=$pallet_shipping_qty[$key];
                }
                $is_pallet0_shipping=true;
            } else if($product_ship_option[$key]=="Pallet 1200mm X 1200mm"){
                $pallet_qty_1+=$val;
                if($pallet_q>$pallet_shipping_qty[$key]){
                    $p_pallet_qty_1+=$pallet_q;
                } else {
                    $p_pallet_qty_1+=$pallet_shipping_qty[$key];
                }
                $is_pallet1_shipping=true;
                if($pallet_shipping_only[$key]==1){
                    $is_pallet1_shipping_only=true;
                }
            } else if($product_ship_option[$key]=="Pallet 1200mm X 1500mm"){
                $pallet_qty_2+=$val;
                if($pallet_q>$pallet_shipping_qty[$key]){
                    $p_pallet_qty_2+=$pallet_q;
                } else {
                    $p_pallet_qty_2+=$pallet_shipping_qty[$key];
                }
                $is_pallet2_shipping=true;
                if($pallet_shipping_only[$key]==1){
                    $is_pallet2_shipping_only=true;
                }
            } else if($product_ship_option[$key]=="Shelter"){
                $rate = $product_ship_cost[$key];
                if($val>1){
                    $rate+= $shipping_cost_shelter_extra;
                }
                $rates[] = (1.0 * $rate);
                //$is_shelter_shipping=true;
            } else if($product_ship_option[$key]=="Free"){
                $is_free_shipping=true;
            } else {
                $rate = $product_ship_cost[$key];
                $qty = $val;
                $rates[] = (1.0 * $rate);
            }
        }
        if($pallet_qty_0>0 && $pallet_qty_0<=2){
            $p_qty_0=1;
        } else if($pallet_qty_0>0){
            if($pallet_qty_0%2==0){
                $p_qty_0=$pallet_qty_0/2;
            } else {
                $p_qty_0=ceil($pallet_qty_0/2);
            }
        }
        if($pallet_qty_1>0 && $pallet_qty_1<=40){
            $p_qty_1=1;
        } else if($pallet_qty_1>0){
            if($pallet_qty_1%40==0){
                $p_qty_1=$pallet_qty_1/40;
            } else {
                $p_qty_1=ceil($pallet_qty_1/40);
            }
        }
        if($pallet_qty_2>0 && $pallet_qty_2<=40){
            $p_qty_2=1;
        } else if($pallet_qty_2>0){
            if($pallet_qty_2%40==0){
                $p_qty_2=$pallet_qty_2/40;
            } else {
                $p_qty_2=ceil($pallet_qty_2/40);
            }
        }
        if($p_pallet_qty_0>$p_qty_0){
            $p_qty_0=$p_pallet_qty_0; 
        }
        if($p_pallet_qty_1>$p_qty_1){
            $p_qty_1=$p_pallet_qty_1; 
        }
        if($p_pallet_qty_2>$p_qty_2){
            $p_qty_2=$p_pallet_qty_2; 
        }

        if($is_pallet0_shipping==true){
            $rate = $this->getConfigData('shipping_cost_'.$ship_zone.'_0');
            $qty = $p_qty_0;
            $rates[] = (1.0 * $rate * $qty);
        }
        if($is_pallet1_shipping==true){
            if($pallet_qty_1>4){
                $rate = $this->getConfigData('shipping_cost_'.$ship_zone.'_1');
            } else {
                if($is_pallet1_shipping_only==true){
                    $rate = $this->getConfigData('shipping_cost_'.$ship_zone.'_1');
                } else {
                    if($pallet_qty_1<=2){
                        $rate = $this->getConfigData('shipping_cost_'.$ship_zone.'_0');
                        $p_qty_1=1;
                    } else if($pallet_qty_1>=3){
                        $rate = $this->getConfigData('shipping_cost_'.$ship_zone.'_0');
                        $p_qty_1=2;
                    }
                }
            }
            if($p_pallet_qty_1>$p_qty_1){
                $p_qty_1=$p_pallet_qty_1; 
            }
            $qty = $p_qty_1;
            $rates[] = (1.0 * $rate * $qty);
        }
        if($is_pallet2_shipping==true){
            if($pallet_qty_2>4){
                $rate = $this->getConfigData('shipping_cost_'.$ship_zone.'_2');
            } else {
                if($is_pallet2_shipping_only==true){
                    $rate = $this->getConfigData('shipping_cost_'.$ship_zone.'_2');
                } else {
                    if($pallet_qty_2<=2){
                        $rate = $this->getConfigData('shipping_cost_'.$ship_zone.'_0');
                        $p_qty_2=1;
                    } else if($pallet_qty_2>=3){
                        $rate = $this->getConfigData('shipping_cost_'.$ship_zone.'_0');
                        $p_qty_2=2;
                    }
                }
            }
            if($p_pallet_qty_2>$p_qty_2){
                $p_qty_2=$p_pallet_qty_2; 
            }
            $qty = $p_qty_2;
            $rates[] = (1.0 * $rate * $qty);
        }


        /*if($pallet_qty>0 && $pallet_qty<3){
            $rates[] = (1.0 * $shipping_cost_yellow_0);
        } else if($pallet_qty>2 && $pallet_qty<10){
            $rates[] = (1.0 * $shipping_cost_yellow_1);
        } else if($pallet_qty>9 && $pallet_qty<41){
            $rates[] = (1.0 * $shipping_cost_yellow_2);
        }*/

        $price += array_sum($rates);

        /*if ($pallet_qty>40) {
            return false;
        }*/

        if ($price<=0 && $is_free_shipping==false) {
            return false;
        }

        if ($request->getFreeShipping() === true) {
            $price = 0.0;
        }

        $method->setCost($price);
        $method->setPrice($price);
        $result->append($method);

        return $result;
    } else {
        return false;
    }
    }

    public function getRate(\Magento\Quote\Model\Quote\Address\RateRequest $request, $zipRange)
    {
        $postcode = $request->getDestPostcode();
        $this->_logger->debug($postcode.'getrate');
        //return $this->collectRates($request);
        //return $this->matrixrateFactory->create()->getRate($request, $zipRange);
    }

    /**
     * @param $quoteId
     * @return \Magento\Quote\Model\Quote
     */
    protected function getCurrentQuote($quoteId)
    {

        return $this->quoteFactory->create()->load($quoteId);
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['shippingperitem' => $this->getConfigData('name')];
    }
}
