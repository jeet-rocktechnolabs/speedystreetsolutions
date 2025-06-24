<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Model\ApplyValidator;

use Amasty\HidePrice\Model\ApplyValidator\HidePriceValidationInterface;
use Amasty\HidePrice\Model\ConfigProvider;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Customer\Model\Session as CustomerSession;

class IgnoreConfigurationValidation implements HidePriceValidationInterface
{

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CustomerSession
     */
    private $session;

    public function __construct(ConfigProvider $configProvider, CustomerSession $session)
    {
        $this->configProvider = $configProvider;
        $this->session = $session;
    }

    public function isHidePrice(ProductInterface $product): ?bool
    {
        $ignoredProductIds = $this->configProvider->getIgnoreProductIds();
        if (in_array((int)$product->getId(), $ignoredProductIds, true)) {
            return false;
        }

        // ignore by customer ID will work only if FPC disabled
        $currentCustomerId = (int)$this->session->getCustomerId();
        if ($currentCustomerId) {
            $ignoredCustomers = $this->configProvider->getIgnoreCustomerIds();
            if (in_array($currentCustomerId, $ignoredCustomers, true)) {
                return false;
            }
        }

        return null;
    }
}
