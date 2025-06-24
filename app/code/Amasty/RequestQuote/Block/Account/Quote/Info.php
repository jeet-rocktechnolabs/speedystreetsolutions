<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Account\Quote;

use Amasty\Base\Model\Serializer;
use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Model\Registry;
use Amasty\RequestQuote\Model\RegistryConstants;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Amasty\RequestQuote\Model\Source\Status;
use Amasty\RequestQuote\Model\Pdf\ComponentChecker;

class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var \Amasty\RequestQuote\Helper\Data
     */
    private $configHelper;

    /**
     * @var PostHelper
     */
    private $postHelper;

    /**
     * @var ComponentChecker
     */
    private $componentChecker;

    public function __construct(
        TemplateContext $context,
        Registry $registry,
        Serializer $serializer,
        DataObjectFactory $dataObjectFactory,
        \Amasty\RequestQuote\Helper\Data $configHelper,
        PostHelper $postHelper,
        ComponentChecker $componentChecker,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->serializer = $serializer;
        $this->dataObjectFactory = $dataObjectFactory;
        parent::__construct($context, $data);
        $this->configHelper = $configHelper;
        $this->postHelper = $postHelper;
        $this->componentChecker = $componentChecker;
    }

    /**
     * @return \Amasty\RequestQuote\Model\Quote
     */
    public function getQuote()
    {
        return $this->coreRegistry->registry(RegistryConstants::AMASTY_QUOTE);
    }

    /**
     * @param QuoteInterface $quote
     *
     * @return string
     */
    public function getDeleteUrl($quote)
    {
        return $this->getUrl('amasty_quote/account/delete', ['quote_id' => $quote->getId()]);
    }

    /**
     * @param QuoteInterface $quote
     *
     * @return string
     */
    public function getCancelUrl($quote)
    {
        return $this->getUrl('amasty_quote/account/cancel', ['quote_id' => $quote->getId()]);
    }

    public function getDownloadPdfUrl(QuoteInterface $quote): string
    {
        return $this->getUrl('amasty_quote/quote/pdf', ['quote_id' => $quote->getId()]);
    }

    /**
     * @param QuoteInterface $quote
     *
     * @return string
     */
    public function isCancelShowed($quote)
    {
        return in_array(
            $quote->getStatus(),
            [
                Status::PENDING,
                Status::APPROVED,
            ]
        );
    }

    /**
     * @return bool
     */
    public function isDeleteShow()
    {
        return false;
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getNotes()
    {
        if (!$this->getData('notes')) {
            if ($remarks = $this->getQuote()->getRemarks()) {
                $remarks = $this->serializer->unserialize($remarks);
                $this->setData('notes', $this->dataObjectFactory->create(['data' => $remarks]));
            } else {
                $this->setData('notes', $this->dataObjectFactory->create());
            }
        }
        return $this->getData('notes');
    }

    /**
     * @return bool
     */
    public function isExpiryColumnShow()
    {
        return $this->configHelper->getExpirationTime() !== null
            && in_array($this->getQuote()->getStatus(), [Status::APPROVED, Status::EXPIRED]);
    }

    /**
     * @return string
     */
    public function getExpiredDate()
    {
        if ($this->getQuote()->getExpiredDate()) {
            return $this->formatDate(
                $this->getQuote()->getExpiredDate(),
                \IntlDateFormatter::MEDIUM,
                true
            );
        }

        return __('N/A');
    }

    public function getPostData(string $url): string
    {
        return $this->postHelper->getPostData($url);
    }

    public function isAllowedPdf(): bool
    {
        return $this->componentChecker->isComponentsExist();
    }
}
