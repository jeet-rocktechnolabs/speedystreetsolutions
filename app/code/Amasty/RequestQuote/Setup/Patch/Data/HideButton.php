<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Setup\Patch\Data;

use Amasty\RequestQuote\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config as Config;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class HideButton implements DataPatchInterface
{
    /**
     * @var EavSetup
     */
    private $eavSetup;

    /**
     * @var Config
     */
    private $eavConfig;

    public function __construct(
        EavSetup $eavSetup,
        Config $eavConfig
    ) {
        $this->eavSetup = $eavSetup;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @return $this
     */
    public function apply()
    {
        if ($this->eavSetup->getAttribute(Product::ENTITY, Data::ATTRIBUTE_NAME_HIDE_BUY_BUTTON)) {
            return $this;
        }

        $this->eavSetup->addAttribute(
            Product::ENTITY,
            Data::ATTRIBUTE_NAME_HIDE_BUY_BUTTON,
            [
                'group' => 'General',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Hide \'Add to Quote\' button',
                'input' => 'boolean',
                'class' => '',
                'source' => Boolean::class,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '0',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => 'simple,configurable,virtual,grouped,downloadable'
            ]
        );
        $this->eavConfig->clear();

        return $this;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
