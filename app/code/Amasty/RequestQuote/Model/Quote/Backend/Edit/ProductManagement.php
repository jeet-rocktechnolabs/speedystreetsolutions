<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote\Backend\Edit;

use Amasty\Base\Model\Serializer;
use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Api\Data\QuoteItemInterface;
use Amasty\RequestQuote\Model\Quote\Item\Updater as ItemUpdater;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as ObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;

class ProductManagement
{
    /**
     * @var Quote
     */
    private $quote;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StockStateInterface
     */
    private $stockState;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    /**
     * @var ItemUpdater
     */
    private $quoteItemUpdater;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var boolean
     */
    private $resetPriceModificators;

    /**
     * @var int|float
     */
    private $surcharge;

    /**
     * @var int|float
     */
    private $discount;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        StockStateInterface $stockState,
        StockRegistryInterface $stockRegistry,
        ObjectFactory $objectFactory,
        ItemUpdater $quoteItemUpdater,
        Serializer $serializer
    ) {
        $this->productRepository = $productRepository;
        $this->stockState = $stockState;
        $this->stockRegistry = $stockRegistry;
        $this->objectFactory = $objectFactory;
        $this->quoteItemUpdater = $quoteItemUpdater;
        $this->serializer = $serializer;
    }

    /**
     * @param int|Product $product
     * @param int $storeId
     * @param array|float|int|DataObject $config
     * @return boolean|void
     * @throws LocalizedException
     */
    public function addProduct($product, $storeId, $config = 1)
    {
        if (!is_array($config) && !$config instanceof DataObject) {
            $config = ['qty' => $config];
        }
        $config = new DataObject($config);

        if (!$product instanceof Product) {
            $productId = $product;

            try {
                $product = $this->productRepository->getById(
                    $productId,
                    false,
                    $storeId
                );
            } catch (NoSuchEntityException $e) {
                throw new LocalizedException(
                    __('We could not add a product to cart by the ID "%1".', $productId)
                );
            }
        }

        $cartCandidates = $product->getTypeInstance()->prepareForCartAdvanced(
            $config,
            $product,
            AbstractType::PROCESS_MODE_FULL
        );

        foreach ($cartCandidates as $candidate) {
            if ($candidate->getTypeId() == 'simple') {
                try {
                    $this->stockState->checkQuoteItemQty(
                        $candidate->getId(),
                        $config->getQty(),
                        $config->getQty(),
                        $config->getQty(),
                        $product->getStore()->getWebsiteId()
                    );
                } catch (NoSuchEntityException $e) {
                    throw new LocalizedException(
                        __($e->getMessage())
                    );
                }
            }
        }

        $stockItem = $this->stockRegistry->getStockItem(
            $product->getId(),
            $this->getQuote()->getStore()->getWebsiteId()
        );
        if ($stockItem->getIsQtyDecimal()) {
            $product->setIsQtyDecimal(1);
        } else {
            $config->setQty((int)$config->getQty());
        }

        $product->addCustomOption('amasty_quote_price', null);

        $item = $this->getQuote()->addProduct(
            $product,
            $config,
            AbstractType::PROCESS_MODE_FULL
        );

        $item->setNoDiscount(1);

        if (is_string($item)) {
            throw new LocalizedException(__($item));
        }
        $item->checkData();

        return true;
    }

    /**
     * @param array $items
     * @throws LocalizedException
     */
    public function updateQuoteItems($items)
    {
        foreach ($items as $itemId => $info) {
            if (!empty($info['configured'])) {
                $item = $this->getQuote()->updateItem($itemId, $this->objectFactory->create($info));
                $info['qty'] = (double)$item->getQty();
            } else {
                $item = $this->getQuote()->getItemById($itemId);
                if (!$item) {
                    continue;
                }
                $info['qty'] = (double)$info['qty'];
            }

            if (isset($info['price'])) {
                $info['price'] = (double)$info['price'];
                if ($this->isResetPriceModificators()) {
                    $info['price'] = $this->getRequestedPrice($item);
                }
            }

            $info['modificators'] = [
                QuoteInterface::SURCHARGE => $this->getSurcharge(),
                QuoteInterface::DISCOUNT => $this->getDiscount()
            ];
            if (!$item->getOptionByCode('amasty_quote_price') && $info['action'] !== 'remove') {
                $priceOption = $this->objectFactory->create(
                    []
                )->setCode(
                    'amasty_quote_price'
                )->setValue(
                    $item->getPrice()
                )->setProduct(
                    $item->getProduct()
                );
                $item->addOption($priceOption);
            }
            $this->quoteItemUpdater->update($item, $info);
            if ($item && !empty($info['action'])) {
                $this->moveQuoteItem($item, $info['action'], $item->getQty());
            }
        }
    }

    /**
     * @param Item $item
     * @return float|null
     */
    private function getRequestedPrice(Item $item)
    {
        $additionalData = $this->serializer->unserialize($item->getAdditionalData()) ?: [];

        return $additionalData[QuoteItemInterface::CUSTOM_PRICE] ?? null;
    }

    /**
     * @param int|Item $item
     * @param string $moveTo
     * @param int $qty
     * @return $this
     */
    public function moveQuoteItem($item, $moveTo, $qty)
    {
        $item = $this->getQuoteItem($item);
        if ($item) {
            $removeItem = false;
            $moveTo = explode('_', $moveTo);
            switch ($moveTo[0]) {
                case 'remove':
                    $removeItem = true;
                    break;
                default:
                    break;
            }
            if ($removeItem) {
                $this->getQuote()->deleteItem($item);
            }
        }

        return $this;
    }

    /**
     * @param int|Item $item
     * @return Item|false
     */
    protected function getQuoteItem($item)
    {
        if ($item instanceof Item) {
            return $item;
        } elseif (is_numeric($item)) {
            return $this->getQuote()->getItemById($item);
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isResetPriceModificators(): bool
    {
        return $this->resetPriceModificators;
    }

    /**
     * @param bool $resetPriceModificators
     */
    public function setResetPriceModificators(bool $resetPriceModificators)
    {
        $this->resetPriceModificators = $resetPriceModificators;
    }

    /**
     * @return float|int
     */
    public function getSurcharge()
    {
        return $this->surcharge;
    }

    /**
     * @param float|int $surcharge
     */
    public function setSurcharge($surcharge)
    {
        $this->surcharge = $surcharge;
    }

    /**
     * @return float|int
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param float|int $discount
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
    }

    /**
     * @return Quote
     */
    public function getQuote(): Quote
    {
        return $this->quote;
    }

    /**
     * @param Quote $quote
     */
    public function setQuote(Quote $quote)
    {
        $this->quote = $quote;
    }
}
