<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Customer\ViewModel;

use Magento\Backend\Model\Session\Quote;
use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Directory\Helper\Data;
use Magento\Framework\Serialize\Serializer\Json;

class AddressFormatter
{
    /**
     * Customer form factory
     *
     * @var FormFactory
     */
    private $customerFormFactory;

    /**
     * Address format helper
     *
     * @var Address
     */
    private $addressFormatHelper;

    /**
     * Directory helper
     *
     * @var Data
     */
    private $directoryHelper;

    /**
     * Session quote
     *
     * @var Quote
     */
    private $session;

    /**
     * Json encoder
     *
     * @var Json
     */
    private $jsonEncoder;

    public function __construct(
        FormFactory $customerFormFactory,
        Address $addressFormatHelper,
        Data $directoryHelper,
        Quote $session,
        Json $jsonEncoder
    ) {
        $this->customerFormFactory = $customerFormFactory;
        $this->addressFormatHelper = $addressFormatHelper;
        $this->directoryHelper = $directoryHelper;
        $this->session = $session;
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * Return customer address array as JSON
     *
     * @param array $addressArray
     *
     * @return string
     */
    public function getAddressesJson(array $addressArray): string
    {
        $data = $this->getEmptyAddressForm();
        foreach ($addressArray as $addressId => $address) {
            $addressForm = $this->customerFormFactory->create(
                'customer_address',
                'adminhtml_customer_address',
                $address
            );
            $data[$addressId] = $addressForm->outputData(
                \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_JSON
            );
        }

        return $this->jsonEncoder->serialize($data);
    }

    /**
     * Represent customer address in 'online' format.
     *
     * @param array $address
     * @return string
     */
    public function getAddressAsString(array $address): string
    {
        $formatTypeRenderer = $this->addressFormatHelper->getFormatTypeRenderer('oneline');
        $result = '';
        if ($formatTypeRenderer) {
            $result = $formatTypeRenderer->renderArray($address);
        }

        return $result;
    }

    /**
     * Return empty address address form
     *
     * @return array
     */
    private function getEmptyAddressForm(): array
    {
        $defaultCountryId = $this->directoryHelper->getDefaultCountry($this->session->getStore());
        $emptyAddressForm = $this->customerFormFactory->create(
            'customer_address',
            'adminhtml_customer_address',
            [\Magento\Customer\Api\Data\AddressInterface::COUNTRY_ID => $defaultCountryId]
        );

        return [0 => $emptyAddressForm->outputData(\Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_JSON)];
    }
}
