<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Adminhtml\Quote\Edit;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class Header extends AbstractEdit
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Customer\Helper\View
     */
    private $customerViewHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Amasty\RequestQuote\Model\Quote\Backend\Session $sessionQuote,
        \Amasty\RequestQuote\Model\Quote\Backend\Edit $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Helper\View $customerViewHelper,
        array $data = []
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerViewHelper = $customerViewHelper;
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if ($this->getSession()->getQuote()->getIncrementId()) {
            return __('Edit Quote #%1', $this->getSession()->getQuote()->getIncrementId());
        }
        $out = $this->getQuoteEditTitle();
        return $this->escapeHtml($out);
    }

    /**
     * @return string
     */
    private function getQuoteEditTitle()
    {
        $customerId = $this->getCustomerId();
        $storeId = $this->getStoreId();
        $out = '';
        if ($customerId && $storeId) {
            $out .= __(
                'Create New Quote for %1 in %2',
                $this->getCustomerName($customerId),
                $this->getStore()->getName()
            );
        } elseif (!$customerId && $storeId) {
            $out .= __('Create New Quote in %1', $this->getStore()->getName());
        } elseif ($customerId && !$storeId) {
            $out .= __('Create New Quote for %1', $this->getCustomerName($customerId));
        } elseif (!$customerId && !$storeId) {
            $out .= __('Create New Quote for New Customer');
        }

        return $out;
    }

    /**
     * @param int $customerId
     * @return string
     */
    private function getCustomerName($customerId)
    {
        $customerData = $this->customerRepository->getById($customerId);
        return $this->customerViewHelper->getCustomerName($customerData);
    }
}
