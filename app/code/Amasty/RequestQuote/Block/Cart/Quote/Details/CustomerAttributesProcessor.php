<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Cart\Quote\Details;

use Amasty\RequestQuote\Model\ConfigProvider;
use Amasty\RequestQuote\Model\Quote\Session as QuoteSession;
use Amasty\RequestQuote\Plugin\CustomerCustomAttributes\Helper\DataPlugin;
use Magento\Checkout\Block\Checkout\AttributeMerger as AttributeMergerBlock;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Customer\Model\Context;
use Magento\Customer\Model\Form as CustomerForm;
use Magento\Customer\Model\FormFactory as CustomerFormFactory;
use Magento\Eav\Model\Attribute;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form\AttributeMapper;
use Psr\Log\LoggerInterface;

class CustomerAttributesProcessor implements LayoutProcessorInterface
{
    public const CUSTOMER_ACCOUNT_CREATE = 'customer_account_create';

    public const ADDITIONAL_ATTRIBUTES = [
        'prefix',
        'firstname',
        'middlename',
        'lastname',
        'suffix',
        'dob',
        'taxvat',
        'gender'
    ];

    /**
     * @var array
     */
    private $forms;

    /**
     * @var CustomerFormFactory
     */
    private $customerFormFactory;

    /**
     * @var AttributeMapper
     */
    private $attributeMapper;

    /**
     * @var AttributeMergerBlock
     */
    private $attributeMerger;

    /**
     * @var QuoteSession
     */
    private $quoteSession;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Escaper
     */
    private $escaper;

    public function __construct(
        CustomerFormFactory $customerFormFactory,
        AttributeMapper $attributeMapper,
        AttributeMergerBlock $attributeMerger,
        QuoteSession $quoteSession,
        HttpContext $httpContext,
        TimezoneInterface $localeDate,
        LoggerInterface $logger,
        ?ConfigProvider $configProvider = null,
        ?UrlInterface $urlBuilder = null,
        ?Escaper $escaper = null
    ) {
        $this->customerFormFactory = $customerFormFactory;
        $this->attributeMapper = $attributeMapper;
        $this->attributeMerger = $attributeMerger;
        $this->quoteSession = $quoteSession;
        $this->httpContext = $httpContext;
        $this->localeDate = $localeDate;
        $this->logger = $logger;
        $this->configProvider = $configProvider ?? ObjectManager::getInstance()->get(ConfigProvider::class);
        $this->urlBuilder = $urlBuilder ?? ObjectManager::getInstance()->get(UrlInterface::class);
        $this->escaper = $escaper ?? ObjectManager::getInstance()->get(Escaper::class);
    }

    /**
     * @param array $jsLayout
     */
    public function process($jsLayout): array
    {
        if ($this->httpContext->getValue(Context::CONTEXT_AUTH)) {
            return $jsLayout;
        }

        $attributesConfig = [];
        $attributesAdditionalConfig = [];

        if ($this->configProvider->isAllowedCreateAccountForGuest()) {
            $this->populateAttributesConfig(
                $attributesConfig,
                $attributesAdditionalConfig,
                $this->getAdditionalAttributes()
            );
            $this->populateAttributesConfig(
                $attributesConfig,
                $attributesAdditionalConfig,
                $this->getAccountForm()->getUserAttributes()
            );
            $this->populateAttributesConfig(
                $attributesConfig,
                $attributesAdditionalConfig,
                $this->getQuoteForm()->getUserAttributes(),
                true
            );
        } else {
            $attributesConfig = $this->getAttributesForGuest();
            $jsLayout['components']['details']['children']['customer-email']['config']['tooltip']['description']
                = $this->escaper->escapeHtml(__(
                    'We\'ll send notifications about the quote to the inserted email address.
              Please note that if you want to track a quote status
              on the website you have to <a href="%1">register</a> before submitting the quote.',
                    $this->urlBuilder->getUrl('customer/account/create')
                ), ['a']);
        }

        $jsLayout['components']['details']['children']['customer-attributes-provider'] = [
            'component' => 'uiComponent'
        ];
        $jsLayout['components']['details']['children']['customer-attributes'] = [
            'component' => 'Amasty_RequestQuote/js/quote/customer/attributes',
            'config' => [
                'displayArea' => 'customer-attributes',
                'quoteId' => $this->quoteSession->getQuote()->getId()
            ],
            'dataScope' => 'data',
            'provider' => 'details.customer-attributes-provider'
        ];

        $customerAttributes = &$jsLayout['components']['details']['children']['customer-attributes'];
        $customerAttributes['children'] = array_values(
            $this->attributeMerger->merge(
                $attributesConfig,
                'details.customer-attributes-provider',
                '',
                $attributesAdditionalConfig
            )
        );

        foreach ($customerAttributes['children'] as &$config) {
            if ($config['component'] === 'Magento_Ui/js/form/components/group') {
                $config['component'] = 'Amasty_RequestQuote/js/quote/customer/group';
            }
            $config['dataScope'] = trim($config['dataScope'], '.');
        }

        return $jsLayout;
    }

    private function populateAttributesConfig(
        array &$attributesConfig,
        array &$additionalConfig,
        array $attributes,
        bool $notRequired = false
    ): void {
        /** @var Attribute $attribute */
        foreach ($attributes as $attribute) {
            if ($attribute
                && !isset($attributesConfig[$attribute->getAttributeCode()])
                && $attribute->getIsVisible()
                && ($notRequired || $attribute->getIsRequired())
            ) {
                try {
                    $attributesConfig[$attribute->getAttributeCode()] = $this->getAttributeConfig($attribute);
                    $additionalConfig[$attribute->getAttributeCode()] = $this->getAdditionalConfig($attribute);
                } catch (LocalizedException $e) {
                    $this->logger->error($e->getMessage());
                }
            }
        }
    }

    private function getAttributeConfig(Attribute $attribute): array
    {
        $config = $this->attributeMapper->map($attribute);

        switch ($attribute->getFrontendInput()) {
            case 'date':
                $defaultValue = $attribute->getDefaultValue()
                    ? $this->localeDate->formatDateTime(
                        $attribute->getDefaultValue(),
                        \IntlDateFormatter::SHORT,
                        \IntlDateFormatter::NONE,
                        null,
                        'UTC',
                        $this->getDateFormat()
                    )
                    : '';
                break;
            default:
                $defaultValue = $attribute->getDefaultValue() ?? '';
        }

        $config['default'] = $defaultValue;

        return $config;
    }

    private function getAdditionalConfig(Attribute $attribute): array
    {
        if ($attribute->getFrontendInput() === 'date') {
            return [
                'config' => [
                    'dateFormat' => $this->getDateFormat()
                ]
            ];
        }

        return ['config' => []];
    }

    /**
     * @return array
     */
    private function getAdditionalAttributes(): array
    {
        return array_map(
            function ($attributeCode) {
                return $this->getAttribute($attributeCode);
            },
            self::ADDITIONAL_ATTRIBUTES
        );
    }

    /**
     * @param string $attributeCode
     * @return bool|Attribute
     */
    private function getAttribute(string $attributeCode)
    {
        return $this->getAccountForm()->setFormCode(self::CUSTOMER_ACCOUNT_CREATE)->getAttribute($attributeCode);
    }

    /**
     * Returns format which will be applied for date field in javascript
     *
     * @return string
     */
    public function getDateFormat()
    {
        $dateFormat = $this->localeDate->getDateFormat();
        /** Escape RTL characters which are present in some locales and corrupt formatting */
        $escapedDateFormat = preg_replace('/[^MmDdYy\/\.\-]/', '', $dateFormat);

        return $escapedDateFormat;
    }

    /**
     * @return CustomerForm
     */
    private function getAccountForm()
    {
        return $this->getFormByCode(self::CUSTOMER_ACCOUNT_CREATE);
    }

    /**
     * @return CustomerForm
     */
    private function getQuoteForm()
    {
        return $this->getFormByCode(DataPlugin::QUOTE_FORM);
    }

    /**
     * @param string $code
     * @return CustomerForm
     */
    private function getFormByCode(string $code)
    {
        if (!isset($this->forms[$code])) {
            $this->forms[$code] = $this->customerFormFactory->create()->setFormCode($code);
        }

        return $this->forms[$code];
    }

    private function getAttributesForGuest(): array
    {
        return [
            'firstname' => [
                'dataType' => 'text',
                'formElement' => 'input',
                'visible' => '1',
                'required' => '1',
                'label' => 'First Name',
                'sortOrder' => '10',
                'notice' => null,
                'default' => '',
                'size' => '0',
                'validation' => [
                    'required-entry' => true,
                    'max_text_length' => 255,
                    'min_text_length' => 1,
                ],
            ],
            'lastname' => [
                'dataType' => 'text',
                'formElement' => 'input',
                'visible' => '1',
                'required' => '1',
                'label' => 'Last Name',
                'sortOrder' => '20',
                'notice' => null,
                'default' => '',
                'size' => '0',
                'validation' => [
                    'required-entry' => true,
                    'max_text_length' => 255,
                    'min_text_length' => 1,
                ]
            ]
        ];
    }
}
