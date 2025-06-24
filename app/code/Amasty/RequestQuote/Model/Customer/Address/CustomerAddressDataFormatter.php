<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Customer\Address;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Customer\Model\Address\Mapper as AddressMapper;
use Magento\Framework\Exception\LocalizedException;

class CustomerAddressDataFormatter
{
    /**
     * @var AddressMapper
     */
    private $addressMapper;

    /**
     * @var AddressConfig
     */
    private $addressConfig;

    /**
     * @var CustomAttributesProcessor
     */
    private $customAttributesProcessor;

    public function __construct(
        AddressMapper $addressMapper,
        AddressConfig $addressConfig,
        CustomAttributesProcessor $customAttributesProcessor
    ) {
        $this->addressMapper = $addressMapper;
        $this->addressConfig = $addressConfig;
        $this->customAttributesProcessor = $customAttributesProcessor;
    }

    /**
     * Prepare customer address data.
     *
     * @param AddressInterface $customerAddress
     * @return array
     * @throws LocalizedException
     */
    public function prepareAddress(AddressInterface $customerAddress)
    {
        $resultAddress = [
            'id' => $customerAddress->getId(),
            'customer_id' => $customerAddress->getCustomerId(),
            'company' => $customerAddress->getCompany(),
            'prefix' => $customerAddress->getPrefix(),
            'firstname' => $customerAddress->getFirstname(),
            'lastname' => $customerAddress->getLastname(),
            'middlename' => $customerAddress->getMiddlename(),
            'suffix' => $customerAddress->getSuffix(),
            'street' => $customerAddress->getStreet(),
            'city' => $customerAddress->getCity(),
            'region' => [
                'region' => $customerAddress->getRegion()->getRegion(),
                'region_code' => $customerAddress->getRegion()->getRegionCode(),
                'region_id' => $customerAddress->getRegion()->getRegionId(),
            ],
            'region_id' => $customerAddress->getRegionId(),
            'postcode' => $customerAddress->getPostcode(),
            'country_id' => $customerAddress->getCountryId(),
            'telephone' => $customerAddress->getTelephone(),
            'fax' => $customerAddress->getFax(),
            'default_billing' => $customerAddress->isDefaultBilling(),
            'default_shipping' => $customerAddress->isDefaultShipping(),
            'inline' => $this->getCustomerAddressInline($customerAddress),
            'custom_attributes' => [],
            'extension_attributes' => $customerAddress->getExtensionAttributes(),
            'vat_id' => $customerAddress->getVatId()
        ];

        if ($customerAddress->getCustomAttributes()) {
            $customerAddress = $customerAddress->__toArray();
            $resultAddress['custom_attributes'] = $this->customAttributesProcessor->filterNotVisibleAttributes(
                $customerAddress['custom_attributes']
            );
        }

        return $resultAddress;
    }

    /**
     * Set additional customer address data
     *
     * @param AddressInterface $address
     * @return string
     */
    private function getCustomerAddressInline(AddressInterface $address): string
    {
        $builtOutputAddressData = $this->addressMapper->toFlatArray($address);
        return $this->addressConfig
            ->getFormatByCode(AddressConfig::DEFAULT_ADDRESS_FORMAT)
            ->getRenderer()
            ->renderArray($builtOutputAddressData);
    }
}
