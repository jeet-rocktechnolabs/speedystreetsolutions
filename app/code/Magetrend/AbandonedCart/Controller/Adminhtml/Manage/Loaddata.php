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

namespace Magetrend\AbandonedCart\Controller\Adminhtml\Manage;

use Magento\Backend\App\Action\Context;

/**
 * Load data sample controller class
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Loaddata extends \Magetrend\AbandonedCart\Controller\Adminhtml\Manage
{
    public $sampleDataManager;

    public $resultJsonFactory;

    public function __construct(
        Context $context,
        \Magetrend\AbandonedCart\Model\SampleDataManager $sampleDataManager,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->sampleDataManager = $sampleDataManager;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $this->sampleDataManager->loadSampleData();
            return $this->resultJsonFactory->create()->setData([
                'success' => 1,
                'message' => __('Sample data has been loaded successful')
            ]);
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            return $this->_error($e->getMessage());
        }
    }

    protected function _error($message)
    {
        return $this->resultJsonFactory->create()->setData([
            'error' => $message
        ]);
    }
}
