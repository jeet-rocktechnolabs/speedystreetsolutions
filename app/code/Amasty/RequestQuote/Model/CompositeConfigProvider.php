<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class CompositeConfigProvider implements ConfigProviderInterface
{
    /**
     * @var array
     */
    private $configProviders;

    public function __construct($configProviders) {
        $this->configProviders = $configProviders;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [];
        foreach ($this->configProviders as $configProvider) {
            $config = array_merge_recursive($config, $configProvider->getConfig());
        }
        return $config;
    }
}
