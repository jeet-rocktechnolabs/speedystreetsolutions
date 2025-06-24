<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote\Backend;

use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Amasty\RequestQuote\Model\Registry;
use Amasty\RequestQuote\Model\RegistryConstants;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;

class QuoteLoadResolver
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Session
     */
    private $quoteSession;

    /**
     * @var QuoteRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var ActionFlag
     */
    private $actionFlag;

    /**
     * @var Registry
     */
    private $coreRegistry;

    public function __construct(
        RequestInterface $request,
        Session $quoteSession,
        QuoteRepositoryInterface $quoteRepository,
        ManagerInterface $messageManager,
        ActionFlag $_actionFlag,
        Registry $coreRegistry
    ) {
        $this->request = $request;
        $this->quoteSession = $quoteSession;
        $this->quoteRepository = $quoteRepository;
        $this->messageManager = $messageManager;
        $this->actionFlag = $_actionFlag;
        $this->coreRegistry = $coreRegistry;
    }

    public function initQuote($editMode = false)
    {
        $id = $this->request->getParam('quote_id');
        try {
            if ($editMode && $this->quoteSession->getChildId($id)) {
                $quote = $this->quoteRepository->getMagentoQuote(
                    $this->quoteSession->getChildId($id),
                    ['*']
                );
            } else {
                $quote = $this->quoteRepository->get($id, ['*']);
            }
            $quote->setIgnoreOldQty(true);
            $quote->setIsSuperMode(true);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This quote no longer exists.'));
            $this->actionFlag->set('', ActionInterface::FLAG_NO_DISPATCH, true);

            return false;
        } catch (InputException $e) {
            $this->messageManager->addErrorMessage(__('This quote no longer exists.'));
            $this->actionFlag->set('', ActionInterface::FLAG_NO_DISPATCH, true);

            return false;
        }

        if ($editMode && !$this->validateSession($quote)) {
            $this->messageManager->addErrorMessage(__('Your session has been expired. Please try again'));
            $this->actionFlag->set('', ActionInterface::FLAG_NO_DISPATCH, true);

            return false;
        }

        $this->coreRegistry->register(RegistryConstants::AMASTY_QUOTE, $quote);
        $this->quoteSession->setQuote($quote);
        $this->quoteSession->setCurrencyCode($quote->getQuoteCurrencyCode());

        return $quote;
    }

    public function validateSession($quote): bool
    {
        $session = $this->quoteSession;

        return $session->getQuoteId() && $quote->getId() == $session->getQuoteId();
    }
}
