<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.3 or later
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */

namespace Magetrend\AbandonedCart\Block\Adminhtml\Widget\Grid\Column\Filter\Bar;

/**
 * Grid column filter event block class
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Event extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select\Extended
{
    /**
     * @var \Magetrend\AbandonedCart\Model\Config\Source\Bar\Event
     */
    public $triggerEvent;

    /**
     * Event constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param \Magetrend\AbandonedCart\Model\Config\Source\Bar\Event $triggerEvent
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Magetrend\AbandonedCart\Model\Config\Source\Bar\Event $triggerEvent,
        array $data = []
    ) {
        $this->triggerEvent = $triggerEvent;
        parent::__construct($context, $resourceHelper, $data);
    }

    protected function _getOptions()
    {
        $options = $this->triggerEvent->toOptionArray();
        $empty = ['value' => '', 'label' => ''];
        array_unshift($options, $empty);
        return $options;
    }

    /**
     * Retrieve condition
     *
     * @return array
     */
    public function getCondition()
    {
        $likeExpression = $this->_resourceHelper->addLikeEscape(','.$this->getValue().',', ['position' => 'any']);
        return ['like' => $likeExpression];
    }
}
