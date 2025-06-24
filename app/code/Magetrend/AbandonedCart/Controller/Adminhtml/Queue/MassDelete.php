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
 * Queue mass delete controller class
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class MassDelete extends \Magetrend\AbandonedCart\Controller\Adminhtml\Queue
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
                $this->queueManager->massDelete($idList);
                // display success message
                $this->messageManager->addSuccess(__('The schedules have been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->messageManager->addError(__('We can\'t find a item to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
