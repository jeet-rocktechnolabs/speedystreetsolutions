<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Observer\Frontend;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Model\Email\Sender;
use Amasty\RequestQuote\Model\Quote;
use Amasty\RequestQuote\Model\QuoteRepository;
use Amasty\RequestQuote\Model\Source\Status;
use Amasty\RequestQuote\Helper\Data as ConfigHelper;
use Amasty\RequestQuote\Helper\Date as DateHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Pricing\Adjustment\Calculator;

class AutoApprove implements ObserverInterface
{
    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var Sender
     */
    private $emailSender;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var Calculator
     */
    private $calculator;

    /**
     * @var DateHelper
     */
    private $dateHelper;

    public function __construct(
        QuoteRepository $quoteRepository,
        Sender $emailSender,
        ConfigHelper $configHelper,
        Calculator $calculator,
        DateHelper $dateHelper
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->emailSender = $emailSender;
        $this->configHelper = $configHelper;
        $this->calculator = $calculator;
        $this->dateHelper = $dateHelper;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @var QuoteInterface $amastyQuote */
        $amastyQuote = $observer->getQuote();
        if ($this->isAutoApproveAllowed($amastyQuote)) {
            $data[QuoteInterface::STATUS] = Status::APPROVED;
            if ($expDays = $this->configHelper->getExpirationTime()) {
                $data[QuoteInterface::EXPIRED_DATE] = $this->dateHelper->increaseDays($expDays);
            }
            if ($remDays = $this->configHelper->getReminderTime()) {
                $data[QuoteInterface::REMINDER_DATE] = $this->dateHelper->increaseDays($remDays);
            }
            $this->quoteRepository->updateData($amastyQuote, $data);
            $this->emailSender->sendApproveEmail($amastyQuote);
        }

        return $this;
    }

    /**
     * @param Quote $quote
     * @return bool
     */
    protected function isAutoApproveAllowed($quote)
    {
        $result = false;
        if ($this->configHelper->isAutoApproveAllowed()) {
            $requestedTotals = 0;
            $originalTotals = 0;
            foreach ($quote->getAllVisibleItems() as $quoteItem) {
                $requestedTotals += $quoteItem->getPrice();
                $originalTotals += $this->getOriginalPrice($quoteItem);
            }
            if ($originalTotals && $originalTotals >= $requestedTotals) {
                $requestedDiscount = ($originalTotals - $requestedTotals) / $originalTotals * 100;
                $result = $requestedDiscount < $this->configHelper->getAllowedPercentForApprove();
            }
        }

        return $result;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @return float
     */
    protected function getOriginalPrice($quoteItem)
    {
        return $this->calculator->getAmount(
            $quoteItem->getProduct()->getFinalPrice(),
            $quoteItem->getProduct()
        )->getValue(['tax', 'weee_tax']);
    }
}
