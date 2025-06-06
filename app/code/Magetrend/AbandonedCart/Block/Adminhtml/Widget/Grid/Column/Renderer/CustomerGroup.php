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

namespace Magetrend\AbandonedCart\Block\Adminhtml\Widget\Grid\Column\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;

/**
 * Grid column renderer customer group block class
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class CustomerGroup extends AbstractRenderer
{
    public $groups = null;

    public $customerGroup;

    /**
     * CustomerGroup constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Magetrend\AbandonedCart\Model\Config\Source\CustomerGroup $customerGroup
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magetrend\AbandonedCart\Model\Config\Source\CustomerGroup $customerGroup,
        array $data = []
    ) {
        $this->customerGroup = $customerGroup;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $groups = $this->getGroups();
        $customerId = $row->getCustomerGroup();
        if (isset($groups[$customerId])) {
            return $groups[$customerId];
        }

        return 'N/A';
    }

    public function getGroups()
    {
        if ($this->groups == null) {
            $this->groups = $this->customerGroup->toArray();
        }
        return $this->groups;
    }
}
