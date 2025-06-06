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

namespace Magetrend\AbandonedCart\Block;

use Magento\Framework\App\Request\Http;

/**
 * Bar Block
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Bar extends \Magento\Framework\View\Element\Template
{
    public $ignoreFrontNames = [];

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    public $request;

    /**
     * General module helper
     *
     * @var \Magetrend\AbandonedCart\Helper\Data
     */
    public $helper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $jsonHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry;

    /**
     * @var \Magetrend\AbandonedCart\Model\ResourceModel\Rule\Collection|null
     */
    public $rules = null;

    /**
     * @var \Magetrend\AbandonedCart\Model\ResourceModel\Rule\CollectionFactory
     */
    public $ruleCollectionFactory;

    public $cart;

    /**
     * Bar constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magetrend\AbandonedCart\Helper\Data $helper
     * @param \Magetrend\AbandonedCart\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param Http $request
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magetrend\AbandonedCart\Helper\Data $helper,
        \Magetrend\AbandonedCart\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        Http $request,
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Helper\Cart $cart,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->jsonHelper = $jsonHelper;
        $this->coreRegistry = $registry;
        $this->request = $request;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->cart = $cart;
        parent::__construct($context, $data);
    }

    /**
     * It returns config for js script in json format
     * @return string
     */
    public function getConfigJs()
    {
        return $this->jsonHelper->jsonEncode($this->getConfig());
    }

    /**
     * It returns configuration for js script
     * @return array
     */
    public function getConfig()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $config = [
            'url' => [
                'load' => $this->getActionUrl('acart/bar/load'),
            ],
            'store_id' => $storeId,
            'front_name' => $this->request->getFullActionName()
        ];
        return $config;
    }

    /**
     * Is message available to show
     *
     * @return bool
     */
    public function isActive()
    {
        if (!$this->helper->isActive()) {
            return false;
        }

        if (in_array($this->request->getFullActionName(), $this->ignoreFrontNames)) {
            return false;
        }

        if ($this->getActiveRules()->getSize() == 0) {
            return false;
        }

        return true;
    }

    public function getActiveRules()
    {
        if ($this->rules == null) {
            $storeId = $this->_storeManager->getStore()->getId();
            $this->rules = $this->ruleCollectionFactory->create()
                ->addFieldToFilter('is_active', 1)
                ->addStoreIdFilter($storeId);
        }

        return $this->rules;
    }

    /**
     * It returns ajax request url
     *
     * @param string $route
     * @param array $params
     * @return mixed|string
     */
    public function getActionUrl($route, $params = [])
    {
        return str_replace(['http:', 'https:'], '', $this->getUrl($route, $params));
    }

    public function getQuoteId()
    {
        $quote = $this->cart->getQuote();
        if ($quote) {
            return $quote->getId();
        }

        return 0;
    }
}
