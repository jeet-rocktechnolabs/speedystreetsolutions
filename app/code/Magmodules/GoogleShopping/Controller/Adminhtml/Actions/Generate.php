<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\GoogleShopping\Controller\Adminhtml\Actions;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Area;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\App\Emulation;
use Magmodules\GoogleShopping\Api\Log\RepositoryInterface as LogRepository;
use Magmodules\GoogleShopping\Exceptions\Validation as ValidationException;
use Magmodules\GoogleShopping\Helper\General as GeneralHelper;
use Magmodules\GoogleShopping\Model\Feed as FeedModel;

class Generate extends Action
{
    /**
     * Authorization level
     */
    public const ADMIN_RESOURCE = 'Magmodules_GoogleShopping::config';

    /**
     * @var FeedModel
     */
    private $feedModel;
    /**
     * @var GeneralHelper
     */
    private $generalHelper;
    /**
     * @var Emulation
     */
    private $appEmulation;
    /**
     * @var RedirectInterface
     */
    private $redirect;
    /**
     * @var LogRepository
     */
    private $logger;

    /**
     * @param Action\Context $context
     * @param FeedModel $feedModel
     * @param GeneralHelper $generalHelper
     * @param Emulation $appEmulation
     * @param RedirectInterface $redirect
     * @param LogRepository $logger
     */
    public function __construct(
        Action\Context $context,
        FeedModel $feedModel,
        GeneralHelper $generalHelper,
        Emulation $appEmulation,
        RedirectInterface $redirect,
        LogRepository $logger
    ) {
        $this->feedModel = $feedModel;
        $this->generalHelper = $generalHelper;
        $this->appEmulation = $appEmulation;
        $this->redirect = $redirect;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Execute function for generation of the GoogleShopping feed in admin.
     */
    public function execute()
    {
        $storeId = $this->getRequest()->getParam('store_id');

        if (!$this->generalHelper->getEnabled()) {
            $errorMsg = __('Please enable the extension before generating the feed.');
            $this->messageManager->addErrorMessage($errorMsg);
        } else {
            try {
                $this->appEmulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);
                $result = $this->feedModel->generateByStore($storeId);
                $this->messageManager->addSuccessMessage(
                    __('Successfully generated a feed with %1 product(s).', $result['qty'])
                );
            } catch (ValidationException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->logger->addErrorLog('Generate', $e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('We can\'t generate the feed right now, please check error log in /var/log/googleshopping.log')
                );
                $this->logger->addErrorLog('Generate', $e->getMessage());
            } finally {
                $this->appEmulation->stopEnvironmentEmulation();
            }
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->redirect->getRefererUrl());
        return $resultRedirect;
    }
}
