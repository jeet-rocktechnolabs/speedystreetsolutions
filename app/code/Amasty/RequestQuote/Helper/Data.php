<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Helper;

use Amasty\RequestQuote\Model\Pdf\ComponentChecker;
use Amasty\RequestQuote\Model\Source\Yesnocustom;
use Amasty\RequestQuote\Block\Pdf\PdfTemplate;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    public const CONFIG_PATH_IS_ACTIVE = 'amasty_request_quote/general/is_active';
    public const CONFIG_PATH_DISPLAY_ON_PDP = 'amasty_request_quote/general/visible_on_pdp';
    public const CONFIG_PATH_DISPLAY_ON_LISTING = 'amasty_request_quote/general/visible_on_plp';
    public const CONFIG_PATH_DISPLAY_FOR_GROUP = 'amasty_request_quote/general/visible_for_groups';
    public const CONFIG_PATH_INFORM_GUEST = 'amasty_request_quote/general/inform_guest';
    public const ATTRIBUTE_NAME_HIDE_BUY_BUTTON = 'hide_quote_buy_button';
    public const CONFIG_PATH_IS_ALLOW_CUSTOMIZE_PRICE = 'amasty_request_quote/general/is_allow_customize_price';
    public const CONFIG_PATH_CUSTOM_RATE_LABEL = 'amasty_request_quote/general/custom_rate_label';
    public const CONFIG_PATH_PDF_ATTACH = 'amasty_request_quote/pdf/pdf_attach';
    public const CONFIG_PATH_TEMPLATE_CONTENT = 'amasty_request_quote/pdf/template_content';

    // @codingStandardsIgnoreStart
    public const CONFIG_PATH_ADMIN_NOTIFY_EMAIL = 'amasty_request_quote/admin_notifications/notify_template';
    public const CONFIG_PATH_CUSTOMER_SUBMIT_EMAIL = 'amasty_request_quote/customer_notifications/customer_template_submit';
    public const CONFIG_PATH_CUSTOMER_APPROVE_EMAIL = 'amasty_request_quote/customer_notifications/customer_template_approve';
    public const CONFIG_PATH_CUSTOMER_NEW_EMAIL = 'amasty_request_quote/customer_notifications/customer_template_create_quote';
    public const CONFIG_PATH_CUSTOMER_EDIT_EMAIL = 'amasty_request_quote/customer_notifications/customer_template_edit_quote';
    public const CONFIG_PATH_CUSTOMER_PROMOTION_EMAIL = 'amasty_request_quote/customer_notifications/customer_template_end_promotion';
    public const CONFIG_PATH_CUSTOMER_CANCEL_EMAIL = 'amasty_request_quote/customer_notifications/customer_template_cancel';
    public const CONFIG_PATH_CUSTOMER_EXPIRED_EMAIL = 'amasty_request_quote/customer_notifications/customer_template_expired';
    public const CONFIG_PATH_CUSTOMER_REMINDER_EMAIL = 'amasty_request_quote/customer_notifications/customer_template_reminder';
    // @codingStandardsIgnoreEnd

    public const CONFIG_PATH_CUSTOMER_OPTION_NAME_PREFIX = 'customer/address/prefix_show';
    public const CONFIG_PATH_CUSTOMER_OPTION_NAME_SUFFIX = 'customer/address/suffix_show';
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $sessionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var PdfTemplate
     */
    private $pdfTemplate;

    /**
     * @var ComponentChecker
     */
    private $componentChecker;

    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        PdfTemplate $pdfTemplate,
        ComponentChecker $componentChecker
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->sessionFactory = $sessionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->pdfTemplate = $pdfTemplate;
        $this->componentChecker = $componentChecker;
    }

    /**
     * @param string $path
     * @param null|string $scopeCode
     * @return string
     */
    public function getModuleConfig($path, $scopeCode = null)
    {
        return $this->scopeConfig->getValue('amasty_request_quote/' . $path, ScopeInterface::SCOPE_STORE, $scopeCode);
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_PATH_IS_ACTIVE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isAllowCustomizePrice()
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_PATH_IS_ALLOW_CUSTOMIZE_PRICE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    private function getCustomerSession()
    {
        return $this->sessionFactory->create();
    }

    /**
     * @return bool
     */
    public function displayByuButtonOnPdp()
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_PATH_DISPLAY_ON_PDP, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function displayByuButtonOnListing()
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_PATH_DISPLAY_ON_LISTING, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isAllowedCustomerGroup()
    {
        $customerGroupId = $this->getCustomerSession()->getCustomerGroupId();
        $allowedGroups = (string)$this->scopeConfig->getValue(
            self::CONFIG_PATH_DISPLAY_FOR_GROUP,
            ScopeInterface::SCOPE_STORE
        );
        return in_array($customerGroupId, explode(',', $allowedGroups));
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        $customerId = $this->getCustomerSession()->getCustomerId();

        return (bool)$customerId
            && $this->getCustomerSession()->checkCustomerId($customerId);
    }

    /**
     * @return bool
     */
    public function isGuestCanQuote()
    {
        return $this->isAllowedCustomerGroup() && !$this->isLoggedIn();
    }

    /**
     * @param $quoteId
     * @return \Magento\Framework\DataObject
     */
    public function getOrderByQuoteId($quoteId)
    {
        $collection = $this->orderCollectionFactory->create();
        $collection->addFieldToFilter('quote_id', $quoteId);
        return $collection->getFirstItem();
    }

    /**
     * @param string $groupName
     * @param null|string $scopeCode
     *
     * @return mixed
     */
    public function getSenderEmail($groupName, $scopeCode = null)
    {
        return $this->getModuleConfig($groupName . '/sender_email_identity', $scopeCode);
    }

    /**
     * @param $route
     * @param array $params
     * @return string
     */
    public function getUrl($route, array $params = [])
    {
        return $this->_getUrl($route, $params);
    }

    public function clearScopeUrl()
    {
        $this->_urlBuilder->setScope(null);
    }

    /**
     * @param string $groupName
     *
     * @return mixed
     */
    public function getSendToEmail($groupName)
    {
        return $this->getModuleConfig($groupName . '/send_to_email');
    }

    /**
     * @return array
     */
    public function getExcludeCategories()
    {
        return explode(',', $this->getModuleConfig('general/exclude_category'));
    }

    /**
     * @inheritdoc
     */
    public function getExpirationTime()
    {
        return $this->getModuleConfig('proposal/expiration_time');
    }

    /**
     * @inheritdoc
     */
    public function getReminderTime()
    {
        return $this->getModuleConfig('proposal/reminder_time');
    }

    /**
     * @return bool
     */
    public function isInformGuests()
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_PATH_INFORM_GUEST, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @inheritdoc
     */
    public function getGuestButtonText()
    {
        $buttonText = $this->getModuleConfig('general/guest_button_text');
        if (!$buttonText) {
            $buttonText = __('Login for quote');
        }

        return $buttonText;
    }

    /**
     * @return bool
     */
    public function isAdminNotificationsByCron()
    {
        return $this->getModuleConfig('admin_notifications/notify') == Yesnocustom::CUSTOM;
    }

    /**
     * @return bool
     */
    public function isAdminNotificationsInstantly()
    {
        return $this->getModuleConfig('admin_notifications/notify') == Yesnocustom::INSTANTLY;
    }

    /**
     * @return string
     */
    public function getCostAttribute()
    {
        return $this->getModuleConfig('general/cost_attr');
    }

    /**
     * @return bool
     */
    public function isAutoApproveAllowed()
    {
        return (bool) $this->getModuleConfig('general/auto_approve') && $this->isAllowCustomizePrice();
    }

    /**
     * @return int
     */
    public function getAllowedPercentForApprove()
    {
        return (int) $this->getModuleConfig('general/percent_for_approve');
    }

    /**
     * @return string
     */
    public function getDisplayCurrency($storeId)
    {
        return $this->scopeConfig->getValue(
            Currency::XML_PATH_CURRENCY_DEFAULT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getRateMethodLabel($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_CUSTOM_RATE_LABEL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isPdfAttach(): bool
    {
        return $this->componentChecker->isComponentsExist()
            && $this->scopeConfig->isSetFlag(self::CONFIG_PATH_PDF_ATTACH, ScopeInterface::SCOPE_STORE);
    }

    public function getTemplateContent(): string
    {
        $template = $this->scopeConfig->getValue(self::CONFIG_PATH_TEMPLATE_CONTENT, ScopeInterface::SCOPE_STORE);
        if (!$template) {
            $template = $this->pdfTemplate->toHtml();
        }

        return $template;
    }

    public function getNamePrefixOptions($storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_CUSTOMER_OPTION_NAME_PREFIX,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getNameSuffixOptions($storeId = null) : ?string
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_CUSTOMER_OPTION_NAME_SUFFIX,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
