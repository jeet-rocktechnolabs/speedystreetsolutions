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

namespace Magetrend\AbandonedCart\Block\Adminhtml\Queue;

/**
 * Backend queue edit grid widget block
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Grid extends \Magento\Backend\Block\Widget\Grid
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    public $resourceConnection;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        array $data = []
    ) {
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Returns queue grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('abandonedcart/queue/grid', ['_current' => true]);
    }

    protected function _prepareCollection()
    {
        parent::_prepareCollection();
        $collection = $this->getCollection();
        $collection->getSelect()->joinLeft(
            ['rule' => $this->resourceConnection->getTableName('mt_ac_rule')],
            'rule.entity_id = main_table.rule_id',
            ['rule_name' => 'rule.name', 'rule_type' => 'rule.type']
        )->joinLeft(
            ['quote' => $this->resourceConnection->getTableName('quote')],
            'quote.entity_id = main_table.quote_id',
            ['customer_group' => 'quote.customer_group_id', 'customer_email' => 'quote.customer_email', 'visitor_hash' => 'quote.visitor_hash']
        )->joinLeft(
            ['visitor' => $this->resourceConnection->getTableName('mt_ac_visitor')],
            'visitor.hash = quote.visitor_hash',
            ['visitor_email' => 'visitor.email']
        );
    }
}
