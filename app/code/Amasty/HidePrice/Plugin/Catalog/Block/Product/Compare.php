<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Plugin\Catalog\Block\Product;

class Compare
{
    /**
     * @var \Amasty\HidePrice\Helper\Data
     */
    private $helper;

    public function __construct(
        \Amasty\HidePrice\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Hide add to Compare link
     * @param $subject
     * @param callable $proceed
     * @return string
     */
    public function aroundToHtml(
        $subject,
        callable $proceed
    ) {
        if ($this->helper->getModuleConfig('information/hide_compare')
            && $this->helper->isApplied($subject->getProduct())
        ) {
            return '';
        }

        return $proceed();
    }
}
