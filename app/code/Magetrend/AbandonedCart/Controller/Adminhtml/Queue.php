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

namespace  Magetrend\AbandonedCart\Controller\Adminhtml;

/**
 * Cart rules abstract controller
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Queue extends \Magento\Backend\App\Action
{
    /**
     * @var \Magetrend\AbandonedCart\Model\RuleFactory
     */
    public $ruleFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * @var \Magetrend\AbandonedCart\Model\Manager\Queue
     */
    public $queueManager;

    /**
     * Campaign constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magetrend\AbandonedCart\Model\RuleFactory $ruleFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magetrend\AbandonedCart\Model\RuleFactory $ruleFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Magetrend\AbandonedCart\Model\Manager\Queue $queueManager
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->coreRegistry = $registry;
        $this->resultPageFactory = $resultPageFactory;
        $this->queueManager = $queueManager;
        parent::__construct($context);
    }

    /** 
     * Dispatch request 
     * 
     * @return void 
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Exit Offer / Manage Rules'));
        $this->_view->renderLayout();
    }

    /**
     * Check if user has enough privileges
     *
     * @return bool
     */
    //@codingStandardsIgnoreLine
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magetrend_AbandonedCart::queue');
    }
}
