<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magmodules\GoogleShopping\Block\Adminhtml\Design;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Header extends Field
{

    public const MODULE_CODE = 'googleshopping-magento2';
    public const MODULE_SUPPORT_LINK = 'https://www.magmodules.eu/help/' . self::MODULE_CODE;

    /**
     * @var string
     */
    protected $_template = 'Magmodules_GoogleShopping::system/config/header.phtml';

    /**
     * @inheritDoc
     */
    public function render(AbstractElement $element): string
    {
        $element->addClass('magmodules');

        return $this->toHtml();
    }

    /**
     * Support link for extension
     *
     * @return string
     */
    public function getSupportLink(): string
    {
        return self::MODULE_SUPPORT_LINK;
    }
}
