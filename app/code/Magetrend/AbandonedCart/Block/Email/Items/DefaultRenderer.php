<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.3 or later
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */

namespace Magetrend\AbandonedCart\Block\Email\Items;

/**
 * Default email items renederer block
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class DefaultRenderer extends \Magento\Sales\Block\Order\Email\Items\Order\DefaultOrder
{
    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    public $imageBuilder;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    public $productHelper;

    public $priceCurrency;

    public $appEmulation;

    public $blockFactory;

    public $productFactory;

    /**
     * DefaultOrder constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Catalog\Helper\Output $productHelper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        array $data = []
    ) {
        $this->imageBuilder = $imageBuilder;
        $this->productHelper = $productHelper;
        $this->priceCurrency = $priceCurrency;
        $this->blockFactory = $blockFactory;
        $this->appEmulation = $appEmulation;
        $this->productFactory = $productFactory;
        parent::__construct($context, $data);
    }

    /**
     * Returns item image html
     *
     * @param $item
     * @return string
     */
    public function getItemImage($item)
    {

        $product = $this->productFactory->create()->load($item->getProduct()->getId());
        if (!$product || empty($product->getImage())) {
            return $this->getProductImagePlaceholder();
        }
        $imageUrl = $this->imageBuilder->setProduct($product)
            ->setImageId('category_page_grid')
            ->create()->getImageUrl();
        return $imageUrl;
    }

    public function getProductImagePlaceholder()
    {
        return $this->getViewFileUrl('Magento_Catalog::images/product/placeholder/small_image.jpg');
    }

    public function getFormatedItemPrice($item)
    {
        return $this->priceCurrency->format(
            $item->getPrice(),
            false,
            \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
            $item->getStore()
        );
    }

    public function formatPrice($price, $store)
    {
        return $this->priceCurrency->format(
            $price,
            false,
            \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
            $store
        );
    }
}
