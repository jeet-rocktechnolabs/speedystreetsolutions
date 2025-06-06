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

namespace Magetrend\AbandonedCart\Controller\Bar;

/**
 * Bar loader ajax controller
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Load extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magetrend\SalesNotification\Model\MessageManager
     */
    public $barManager;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    public $resultJsonFactory;

    /**
     * Load constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magetrend\AbandonedCart\Model\Manager\Bar $barManager
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magetrend\AbandonedCart\Model\Manager\Bar $barManager,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->barManager = $barManager;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $response = [
            'success' => false,
            'data' => []
        ];
        if ($this->getRequest()->getParam('store_id')) {
            $request = $this->getRequest()->getParams();
            try {
                $messageData = $this->barManager->getBarHtml($request);
                if (!empty($messageData)) {
                    $response['success'] = true;
                    $response['data'] = $messageData;
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $response['error'] = __('%1', $e->getMessage());
            } catch (\Exception $e) {
                $response['error'] = __('%1', $e->getMessage());
            }
        }
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
