<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Controller\Adminhtml\Quote;

use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Amasty\RequestQuote\Model\Pdf\ComponentChecker;
use Amasty\RequestQuote\Model\Pdf\PdfProvider;
use Amasty\RequestQuote\Model\Registry;
use Amasty\RequestQuote\Model\RegistryConstants;
use Magento\Backend\App\Action\Context;
use Magento\Store\Model\App\Emulation;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Psr\Log\LoggerInterface;

class Pdf extends \Magento\Backend\App\Action
{
    public const ADMIN_RESOURCE = 'Amasty_RequestQuote::pdfDownload';

    /**
     * @var PdfProvider
     */
    private $pdfProvider;

    /**
     * @var ComponentChecker
     */
    private $componentChecker;

    /**
     * @var QuoteRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Emulation
     */
    private $appEmulation;

    public function __construct(
        Context $context,
        PdfProvider $pdfProvider,
        ComponentChecker $componentChecker,
        LoggerInterface $logger,
        QuoteRepositoryInterface $quoteRepository,
        Registry $registry,
        Emulation $appEmulation
    ) {
        $this->pdfProvider = $pdfProvider;
        $this->componentChecker = $componentChecker;
        $this->quoteRepository = $quoteRepository;
        $this->registry = $registry;
        $this->logger = $logger;
        $this->appEmulation = $appEmulation;
        parent::__construct($context);
    }

    /**
     * @return Redirect|\Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        if (!$this->componentChecker->isComponentsExist()) {
            $this->messageManager->addErrorMessage($this->componentChecker->getComponentsErrorMessage());

            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setRefererUrl();
            return $resultRedirect;
        }

        try {
            $quoteId = (int)$this->_request->getParam('quote_id');
            $quote = $this->quoteRepository->get($quoteId, ['*']);
            $this->registry->register(RegistryConstants::AMASTY_QUOTE, $quote);
            //phpcs:ignore Magento2.Legacy.ObsoleteResponse.LoadLayoutResponseMethodFound
            $this->_view->loadLayout();

            $this->appEmulation->startEnvironmentEmulation($quote->getStoreId());
            $raw = $this->pdfProvider->getRawPdf($quote);
            $this->appEmulation->stopEnvironmentEmulation();

            return $raw;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred. The PDF was not downloaded.'));
            $this->logger->error($e->getMessage());
            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setRefererUrl();
            return $resultRedirect;
        }
    }
}
