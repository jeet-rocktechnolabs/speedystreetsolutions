<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Plugin\Wishlist\Model;

use Amasty\HidePrice\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Wishlist\Model\Wishlist;
use Magento\Catalog\Model\ProductRepository;

class WishlistPlugin
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    public function __construct(
        Data $helper,
        ProductRepository $productRepository
    ) {
        $this->helper = $helper;
        $this->productRepository = $productRepository;
    }

    /**
     * @param Wishlist $subject
     * @param $product
     * @param null $buyRequest
     * @param bool $forciblySetQty
     *
     * @return array
     * @throws LocalizedException
     */
    public function beforeAddNewItem(Wishlist $subject, $product, $buyRequest = null, $forciblySetQty = false)
    {
        if (!$product instanceof Product) {
            $product = $this->productRepository->getById($product);
        }

        if ($this->helper->getHideWishlist() && $this->helper->isApplied($product)) {
            throw new LocalizedException(__('%1 can\'t be added to your wishlist', $product->getName()));
        }

        return [$product, $buyRequest, $forciblySetQty];
    }
}
