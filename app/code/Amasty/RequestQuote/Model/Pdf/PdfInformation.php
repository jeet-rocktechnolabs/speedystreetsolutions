<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Pdf;

use Amasty\RequestQuote\Block\Pdf\Items;
use Amasty\RequestQuote\Model\Quote;
use Amasty\RequestQuote\Model\Registry;
use Amasty\RequestQuote\Model\RegistryConstants;
use Amasty\RequestQuote\Model\Source\Status;
use Magento\Customer\Model\ResourceModel\AddressRepository;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Address;
use Magento\Store\Model\Information;

class PdfInformation
{
    /**
     * @var Quote
     */
    private $quote;

    /**
     * @var array
     */
    private $variables;

    /**
     * @var Address
     */
    private $billing;

    /**
     * @var Address
     */
    private $shipping;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var CountryInformationAcquirerInterface
     */
    private $countryInformationAcquirer;

    /**
     * @var Status
     */
    private $status;

    /**
     * @var Items
     */
    private $productGrid;

    /**
     * @var AddressRepository
     */
    private $addressRepository;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        DataObjectFactory $dataObjectFactory,
        CountryInformationAcquirerInterface $countryInformationAcquirer,
        Status $status,
        Items $productGrid,
        AddressRepository $addressRepository,
        Registry $registry,
        ScopeConfigInterface $scopeConfig,
        array $variables = []
    ) {
        $this->variables = $variables;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->countryInformationAcquirer = $countryInformationAcquirer;
        $this->status = $status;
        $this->productGrid = $productGrid;
        $this->addressRepository = $addressRepository;
        $this->registry = $registry;
        $this->scopeConfig = $scopeConfig;
    }

    public function getQuoteDataForPdf(): array
    {
        $data = [];
        $this->quote = $this->registry->registry(RegistryConstants::AMASTY_QUOTE);
        $this->getCustomerInfo($data);

        return $data;
    }

    private function getCustomerInfo(array &$data): void
    {
        foreach ($this->variables as $variableData) {
            $object = $this->getObjectWithData($variableData['objectType']);
            $params = $variableData['params'] ?? [];
            $data[$variableData['variable']] = (string) $object->{$variableData['method']}(...$params);
        }
    }

    /**
     * @param string $objectType
     * @return PdfInformation|Quote|\Magento\Customer\Api\Data\AddressInterface|\Magento\Framework\DataObject|Address
     */
    private function getObjectWithData(string $objectType)
    {
        switch ($objectType) {
            case 'quote':
                $result = $this->quote;
                break;
            case 'billing':
                $result = $this->getDefaultBilling();
                break;
            case 'shipping':
                $result = $this->getDefaultShipping();
                break;
            default:
                $result = $this;
        }

        return $result;
    }

    /**
     * @return \Magento\Customer\Api\Data\AddressInterface|\Magento\Framework\DataObject|Address
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getDefaultBilling()
    {
        if (!$this->billing) {
            $this->billing = $this->quote->getBillingAddress();
            if (!$this->billing->getId()) {
                $this->billing = $this->quote->getCustomer()->getDefaultBilling();
            }
        }

        return $this->billing;
    }

    /**
     * @return \Magento\Quote\Model\Quote\Address|\Magento\Customer\Api\Data\AddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getDefaultShipping()
    {
        if (!$this->shipping) {
            $this->shipping = $this->quote->getShippingAddress();
            if (!$this->shipping->getId()) {
                $this->shipping = $this->quote->getCustomer()->getDefaultShipping();
            }
        }

        return $this->shipping;
    }

    /**
     * @param int $id
     * @return \Magento\Customer\Api\Data\AddressInterface|\Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAddressById(int $id)
    {
        try {
            $address = $this->addressRepository->getById($id);
        } catch (NoSuchEntityException $e) {
            $address = $this->dataObjectFactory->create();
        }

        return $address;
    }

    private function getBillingCountryName(): ?string
    {
        return $this->prepareCountryName($this->getDefaultBilling());
    }

    private function getShippingCountryName(): ?string
    {
        return $this->prepareCountryName($this->getDefaultShipping());
    }

    /**
     * @param \Magento\Customer\Api\Data\AddressInterface|\Magento\Framework\DataObject|Address $address
     * @return string
     */
    private function prepareCountryName($address): string
    {
        $countryName = '';
        $countryId = $address->getCountryId();

        if ($countryId) {
            $countryInformation = $this->countryInformationAcquirer->getCountryInfo($countryId);
            $countryName = $countryInformation->getFullNameLocale();
        }

        return $countryName;
    }

    private function getBillingStreet(): ?string
    {
        return $this->prepareStreet($this->getDefaultBilling());
    }

    private function getShippingStreet(): ?string
    {
        return $this->prepareStreet($this->getDefaultShipping());
    }

    private function getTelephone(): ?string
    {
        $telephone = $this->getDefaultShipping()->getTelephone();
        if ($telephone) {
            //phpcs:ignore
            return '<span>' . __('T') . '</span>' . $telephone;
        }
        return null;
    }

    private function hasShippingInfo(): bool
    {
        return $this->getShippingStreet()
            || $this->getShippingRegion()
            || $this->getShippingCountryName()
            || $this->getDefaultShipping()->getCity()
            || $this->getDefaultShipping()->getPostcode()
            || $this->getTelephone();
    }

    /**
     * @param \Magento\Customer\Api\Data\AddressInterface|\Magento\Framework\DataObject|Address $address
     * @return string|null
     */
    private function prepareStreet($address): ?string
    {
        $street = $address->getStreet();

        return is_array($street) ? implode(',', $street) : $street;
    }

    private function getProductGrid(): ?string
    {
        return $this->productGrid->setQuote($this->quote)->toHtml();
    }

    private function getShippingMethod(): ?string
    {
        return $this->quote->getShippingAddress()->getShippingDescription();
    }

    private function getBillingRegion(): string
    {
        return $this->getRegion($this->getDefaultBilling()->getRegion());
    }

    private function getShippingRegion(): string
    {
        return $this->getRegion($this->getDefaultShipping()->getRegion());
    }

    /**
     * @param \Magento\Customer\Api\Data\RegionInterface|string $region
     * @return string
     */
    private function getRegion($region): string
    {
        $result = '';
        if ($region) {
            $result = is_string($region) ? $region : $region->getRegion();
        }

        return (string)$result;
    }

    private function getStorePhone(): string
    {
        $storeId = (int)$this->quote->getStoreId();
        $configValue = $this->scopeConfig->getValue(
            Information::XML_PATH_STORE_INFO_PHONE,
            'store',
            $storeId
        );
        return $configValue ?: '';
    }
}
