<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Controller\Adminhtml;

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;

abstract class Request extends \Magento\Backend\App\Action
{
    public const CURRENT_REQUEST_MODEL = 'amasty_hideprice_request_model';

    /**
     * @var \Amasty\HidePrice\Model\RequestRepository
     */
    protected $requestRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Amasty\HidePrice\Model\RequestRepository $requestRepository,
        \Magento\Framework\Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->requestRepository = $requestRepository;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_HidePrice::request';

    /**
     * Initiate action
     *
     * @return Page
     */
    protected function _initAction()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE);
        $resultPage->addBreadcrumb(__('Amasty Hide Price'), __('Amasty Hide Price'));
        $resultPage->getConfig()->getTitle()->prepend(__('Requests'));

        return $resultPage;
    }
}
