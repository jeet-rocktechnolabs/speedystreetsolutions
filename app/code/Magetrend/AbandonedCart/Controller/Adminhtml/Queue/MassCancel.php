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

namespace Magetrend\AbandonedCart\Controller\Adminhtml\Queue;

/**
 * Queue mass cancel controller class
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class MassCancel extends \Magetrend\AbandonedCart\Controller\Adminhtml\Queue
{
    /**
     * Process change request
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $idList = $this->getRequest()->getParam('queue', []);
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!empty($idList)) {
            try {
                $this->queueManager->massStatusChange($idList, \Magetrend\AbandonedCart\Model\Queue::STATUS_CANCELED);
                // display success message
                $this->messageManager->addSuccess(__('Items statuses have been updated.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->messageManager->addError(__('We can\'t find a item to update.'));
        return $resultRedirect->setPath('*/*/');
    }
}
