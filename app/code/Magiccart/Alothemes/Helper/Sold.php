<?php
/**
 * Magiccart 
 * @category    Magiccart 
 * @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license     http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2018-06-7 20:26:27
 * @@Modify Date: 2020-03-30 00:26:46
 * @@Function:
 */

namespace Magiccart\Alothemes\Helper;

class Sold extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    protected $_reportCollectionFactory;
    protected $stockRegistry;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        // \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Reports\Model\ResourceModel\Product\Sold\CollectionFactory  $reportCollectionFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    ) {
    	// $this->_localeDate = $localeDate;
    	$this->_reportCollectionFactory = $reportCollectionFactory;
        $this->stockRegistry = $stockRegistry;
        parent::__construct($context);
    }

    public function getSoldQty($product)
    {
    	$productId = $product->getId();
    	$from 	= $product->getData('special_from_date');
    	$to  	= $product->getData('special_to_date');

        // $todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        // $todayEndOfDayDate = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');

        $SoldProducts= $this->_reportCollectionFactory->create();
        // $SoldProdudctCOl=$SoldProducts->addOrderedQty()
        $SoldProdudctCOl=$SoldProducts->addOrderedQty($from, $to)
        ->addAttributeToFilter('product_id', $productId);

        $sold = 0;
	        if($SoldProdudctCOl->count()){
	        foreach ($SoldProdudctCOl as $item) {
	        	$sold += (int) $item->getData('ordered_qty');
	        }
        }
        // echo $SoldProdudctCOl->getSelect()->__toString(); // echo sql

        return $sold;
    }

    public function getProductQty($_product)
    {
        $qty = 0;
        if($_product->getTypeId() == 'simple'){
            $stockItem = $_product->getExtensionAttributes()->getStockItem();
            $qty       = (int) $stockItem->getQty();
            return $qty;
        }
        $allProducts = $_product->getTypeInstance()->getUsedProducts($_product, null);
        foreach ($allProducts as $product) {
            if ($product->isSaleable()) {
                $stockItem = $this->stockRegistry->getStockItem(
                        $product->getId(),
                        $product->getStore()->getWebsiteId()
                    );
                $qty    += (int) $stockItem->getQty();
            }
        }
        return $qty;
    }

}
