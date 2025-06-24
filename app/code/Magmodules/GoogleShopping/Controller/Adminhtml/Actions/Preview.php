<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\GoogleShopping\Controller\Adminhtml\Actions;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Area;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\App\Emulation;
use Magmodules\GoogleShopping\Api\Log\RepositoryInterface as LogRepository;
use Magmodules\GoogleShopping\Model\Feed as FeedModel;
use Magmodules\GoogleShopping\Helper\General as GeneralHelper;
use Magmodules\GoogleShopping\Exceptions\Validation as ValidationException;
use Magento\Framework\Filesystem\Driver\File;

class Preview extends Action
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
     * @var File
     */
    private $driver;
    /**
     * @var LogRepository
     */
    private $logger;

    /**
     * @param Context $context
     * @param FeedModel $feedModel
     * @param GeneralHelper $generalHelper
     * @param Emulation $appEmulation
     * @param RedirectInterface $redirect
     * @param File $driver
     * @param LogRepository $logger
     */
    public function __construct(
        Context $context,
        FeedModel $feedModel,
        GeneralHelper $generalHelper,
        Emulation $appEmulation,
        RedirectInterface $redirect,
        File $driver,
        LogRepository $logger
    ) {
        $this->feedModel = $feedModel;
        $this->generalHelper = $generalHelper;
        $this->appEmulation = $appEmulation;
        $this->redirect = $redirect;
        $this->driver = $driver;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Execute function for preview of the GoogleShopping feed in admin.
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
                $page = $this->getRequest()->getParam('page', 1);
                $productId = $this->getRequest()->getParam('pid', []);
                $data = $this->getRequest()->getParam('data', 0);
                if ($result = $this->feedModel->generateByStore($storeId, 'preview', $productId, $page, $data)) {
                    $this->getResponse()->setHeader('Content-type', 'text/xml');
                    return $this->getResponse()->setBody(
                        $this->driver->fileGetContents($result['path'])
                    );
                } else {
                    $errorMsg = __('Unknown error.');
                    $this->messageManager->addErrorMessage($errorMsg);
                }
            } catch (ValidationException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->logger->addErrorLog('Generate', $e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('We can\'t generate the feed right now, please check error log')
                );
                $this->logger->addErrorLog('Generate', $e->getMessage());
            } finally {
                $this->appEmulation->stopEnvironmentEmulation();
            }
        }

        $this->appEmulation->stopEnvironmentEmulation();

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->redirect->getRefererUrl());
        return $resultRedirect;
    }
}
