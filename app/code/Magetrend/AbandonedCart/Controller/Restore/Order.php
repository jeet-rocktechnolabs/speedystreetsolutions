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

namespace Magetrend\AbandonedCart\Controller\Restore;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;

/**
 * Order restore link controller
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Order extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magetrend\AbandonedCart\Model\Manager\Restore
     */
    public $restoreManager;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    public $resultRedirectFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    public $logger;

    /**
     * Cart constructor.
     * @param Context $context
     * @param \Magetrend\AbandonedCart\Model\Manager\Restore $restoreManager
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magetrend\AbandonedCart\Model\Manager\Restore $restoreManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->restoreManager = $restoreManager;
        $this->logger = $logger;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        parent::__construct($context);
    }

    /**
     * New subscription action
     *
     * @throws \Magento\Framework\Exception\LocalizedExceptioncd
     * @return void
     */
    public function execute()
    {
        try {
            if ($cartHash = $this->getRequest()->getParam('oid', false)) {
                if ($this->restoreManager->restoreOrder($cartHash)) {
                    $this->restoreManager->logEvent(
                        $this->getRequest()->getParam('msg', ''),
                        \Magetrend\AbandonedCart\Model\Queue::STATUS_ORDER_RESTORED,
                        true
                    );
                    return $this->resultRedirectFactory->create()->setPath('checkout');
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(
                __('An error occurred while processing your request. Please try again later.')
            );
        }
        return $this->resultRedirectFactory->create()->setPath('/');
    }
}
