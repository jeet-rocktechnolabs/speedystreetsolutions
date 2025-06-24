<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Observer\Frontend;

use Amasty\RequestQuote\Model\Quote\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;

class LoadCustomerQuoteObserver implements ObserverInterface
{
    /**
     * @var Session
     */
    private $quoteSession;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    public function __construct(
        Session $quoteSession,
        ManagerInterface $messageManager
    ) {
        $this->quoteSession = $quoteSession;
        $this->messageManager = $messageManager;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            $this->quoteSession->loadCustomerQuote();
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Load customer amasty quote error'));
        }
    }
}
