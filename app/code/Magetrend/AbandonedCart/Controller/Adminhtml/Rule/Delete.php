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

namespace Magetrend\AbandonedCart\Controller\Adminhtml\Rule;

/**
 * Rule delete controller class
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Delete extends \Magetrend\AbandonedCart\Controller\Adminhtml\Rule
{
    /**
     * Process delete request
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                // init model and delete
                $model = $this->ruleFactory->create()
                    ->load($id);
                $type = $model->getType();
                $model->delete();
                // display success message
                $this->messageManager->addSuccess(__('The rule has been deleted.'));

                switch ($type) {
                    case \Magetrend\AbandonedCart\Model\Rule::TYPE_BAR:
                        $path = '*/rule/bar_index';
                        break;
                    case \Magetrend\AbandonedCart\Model\Rule::TYPE_ABANDONED_CART:
                        $path = '*/rule/cart_index';
                        break;
                    case \Magetrend\AbandonedCart\Model\Rule::TYPE_FOLLOW_UP:
                        $path = '*/rule/order_index';
                        break;
                    default:
                        $path = '*/*/';
                }

                return $resultRedirect->setPath($path);
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a rule to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
