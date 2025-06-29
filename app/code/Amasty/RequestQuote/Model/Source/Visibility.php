<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Source;

class Visibility implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 1, 'label' => __('Show')], ['value' => 0, 'label' => __('Hide')]];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [0 => __('Hide'), 1 => __('Show')];
    }
}
