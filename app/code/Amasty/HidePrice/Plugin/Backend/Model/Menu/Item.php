<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Plugin\Backend\Model\Menu;

class Item
{
    public function aroundGetUrl(
        \Magento\Backend\Model\Menu\Item $subject,
        callable $proceed
    ) {
        $result = $proceed();
        /* hack for having correct url key ( we cant add params in menu.xml file)*/
        if ($subject->getId() == 'Amasty_HidePrice::settings') {
            $find = 'admin/system_config/edit';
            $result = str_replace($find, $find . '/section/amasty_hide_price', $result);
        }
        return $result;
    }
}
