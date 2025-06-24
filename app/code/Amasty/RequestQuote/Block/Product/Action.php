<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Product;

use Amasty\RequestQuote\Model\Product\IsButtonShow;

class Action extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_RequestQuote::product/addtoquote.phtml';

    /**
     * @var \Amasty\RequestQuote\Helper\Cart
     */
    private $cartHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Amasty\RequestQuote\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    private $category;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var IsButtonShow
     */
    private $isButtonShow;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request,
        \Amasty\RequestQuote\Helper\Data $helper,
        \Amasty\RequestQuote\Helper\Cart $cartHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        IsButtonShow $isButtonShow,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->registry = $registry;
        $this->request = $request;
        $this->helper = $helper;
        $this->cartHelper = $cartHelper;
        $this->productFactory = $productFactory;
        $this->categoryFactory = $categoryFactory;
        $this->isButtonShow = $isButtonShow;
    }

    /**
     * @return string
     */
    public function getAddUrl()
    {
        if (!$this->isProductListing()) {
            return $this->cartHelper->getAddUrl($this->getProduct());
        }

        return '';
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        $html = '';
        $canDisplayButton = $this->isProductListing()
            ? $this->helper->displayByuButtonOnListing()
            : $this->helper->displayByuButtonOnPdp();

        if ($canDisplayButton && $this->isButtonShow->execute($this->getProduct())) {
            if ($this->helper->isAllowedCustomerGroup()) {
                $this->setButtonText(__('Get A Quote'));
                $this->setLoggedIn(true);
                $html = parent::toHtml();
            } elseif (!$this->helper->isLoggedIn() && $this->helper->isInformGuests()) {
                $this->setIsLoggedButton(true);
                $this->setButtonText($this->getGuestButtonText());
                $this->setLoggedIn(false);
                $html = parent::toHtml();
            }
        }

        return $html;
    }

    /**
     * @return bool
     */
    public function isGuest()
    {
        return !$this->helper->isLoggedIn();
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if ($product = $this->registry->registry('current_product')) {
            $this->product = $product;
        } else {
            $this->product = $this->getParentBlock()->getProduct();
        }

        if (!$this->product instanceof \Magento\Catalog\Model\Product) {
            $this->product = $this->productFactory->create();
        }

        return $this->product;
    }

    /**
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategory()
    {
        if ($product = $this->registry->registry('current_category')) {
            $this->category = $product;
        }

        if (!$this->category instanceof \Magento\Catalog\Model\Product) {
            $this->category = $this->productFactory->create();
        }

        return $this->category;
    }

    public function isProductListing(): bool
    {
        $isListing = false;

        if ($this->registry->registry('current_category') !== null
            && !$this->registry->registry('current_product')
        ) {
            $isListing = true;
        } elseif ($this->getRequest()->getFullActionName() == 'catalogsearch_result_index') {
            $isListing = true;
        }

        return $isListing;
    }

    /**
     * @return string
     */
    public function getGuestButtonText()
    {
        return $this->escapeHtml($this->helper->getGuestButtonText());
    }
}
