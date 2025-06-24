<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Customer\ViewModel;

use Amasty\Base\Model\MagentoVersion;
use Amasty\RequestQuote\Model\Customer\ViewModel\AddressFormatter as AmastyAddressDataFormatter;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\ViewModel\Customer\AddressFormatter as MagentoAddressDataFormatter;

class AddressFormatterFactory implements ArgumentInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var MagentoVersion
     */
    private $magentoVersion;

    public function __construct(MagentoVersion $magentoVersion, ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
        $this->magentoVersion = $magentoVersion;
    }

    /**
     * @param array $data
     * @return AmastyAddressDataFormatter|MagentoAddressDataFormatter
     */
    public function create(array $data = [])
    {
        if (version_compare($this->magentoVersion->get(), '2.3.1', '<')) {
            $instanceName = AmastyAddressDataFormatter::class;
        } else {
            $instanceName = MagentoAddressDataFormatter::class;
        }

        return $this->objectManager->create($instanceName, $data);
    }
}
