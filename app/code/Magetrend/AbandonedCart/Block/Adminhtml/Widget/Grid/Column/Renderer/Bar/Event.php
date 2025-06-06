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

namespace Magetrend\AbandonedCart\Block\Adminhtml\Widget\Grid\Column\Renderer\Bar;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;

/**
 * Grid column renderer evemt block class
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Event extends AbstractRenderer
{
    public $event;

    public $eventList = null;

    /**
     * Event constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Magetrend\AbandonedCart\Model\Config\Source\Bar\Event $event
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magetrend\AbandonedCart\Model\Config\Source\Bar\Event $event,
        array $data = []
    ) {
        $this->event = $event;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $eventList = $this->getEventList();
        $events = $row->getTriggerEvents();
        $extractedEvent = explode(',', trim(rtrim($events, ','), ','));
        if (empty($extractedEvent)) {
            return 'N/A';
        }
        $html = '';
        foreach ($extractedEvent as $index) {
            if (isset($eventList[$index])) {
                $html .= $eventList[$index].'<br/>';
            }
        }

        return $html;
    }

    public function getEventList()
    {
        if ($this->eventList == null) {
            $this->eventList = $this->event->toArray();
        }
        return $this->eventList;
    }
}
