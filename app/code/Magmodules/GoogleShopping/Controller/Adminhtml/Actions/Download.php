<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\GoogleShopping\Controller\Adminhtml\Actions;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filesystem\Io\File;
use Magmodules\GoogleShopping\Api\Log\RepositoryInterface as LogRepository;
use Magmodules\GoogleShopping\Helper\Feed as FeedHelper;
use Magmodules\GoogleShopping\Helper\General as GeneralHelper;

class Download extends Action
{

    /**
     * Authorization level
     */
    public const ADMIN_RESOURCE = 'Magmodules_GoogleShopping::config';

    /**
     * Error message if file is not found
     */
    public const ERR = 'We can\'t generate the feed right now, please check error log in /var/log/googleshopping.log';

    /**
     * @var FeedHelper
     */
    private $feedHelper;
    /**
     * @var GeneralHelper
     */
    private $generalHelper;
    /**
     * @var FileFactory
     */
    private $fileFactory;
    /**
     * @var RawFactory
     */
    private $resultRawFactory;
    /**
     * @var RedirectInterface
     */
    private $redirect;
    /**
     * @var File
     */
    private $fileSystemIo;
    /**
     * @var LogRepository
     */
    private $logger;

    /**
     * @param Action\Context $context
     * @param RawFactory $resultRawFactory
     * @param FileFactory $fileFactory
     * @param FeedHelper $feedHelper
     * @param GeneralHelper $generalHelper
     * @param RedirectInterface $redirect
     * @param File $fileSystemIo
     */
    public function __construct(
        Action\Context $context,
        RawFactory $resultRawFactory,
        FileFactory $fileFactory,
        FeedHelper $feedHelper,
        GeneralHelper $generalHelper,
        RedirectInterface $redirect,
        File $fileSystemIo,
        LogRepository $logger
    ) {
        $this->feedHelper = $feedHelper;
        $this->fileFactory = $fileFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->generalHelper = $generalHelper;
        $this->redirect = $redirect;
        $this->fileSystemIo = $fileSystemIo;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Execute function for download of the GoogleShopping feed in admin.
     */
    public function execute()
    {
        $storeId = $this->getRequest()->getParam('store_id');
        $feed = $this->feedHelper->getFeedLocation($storeId);

        if (!empty($feed['full_path'])) {
            try {
                $fileInfo = $this->fileSystemIo->getPathInfo($feed['full_path']);
                $this->fileFactory->create(
                    $fileInfo['basename'],
                    [
                        'type' => 'filename',
                        'value' => 'googleshopping/' . $fileInfo['basename'],
                        'rm' => false,
                    ],
                    DirectoryList::MEDIA,
                    'application/octet-stream',
                    null
                );
                return $this->resultRawFactory->create();
            } catch (\Exception $e) {
                $errorMessage = self::ERR;
                $this->messageManager->addExceptionMessage($e, __($errorMessage));
                $this->logger->addErrorLog('Generate', $e->getMessage());
            }
        }
        $this->messageManager->addErrorMessage(__('File not found, please generate new feed.'));

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->redirect->getRefererUrl());
        return $resultRedirect;
    }
}
