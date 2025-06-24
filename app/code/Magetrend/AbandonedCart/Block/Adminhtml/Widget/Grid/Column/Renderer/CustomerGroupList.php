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

/**
 * Grid column renderer customer group list block class
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class CustomerGroupList extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Store
{
    public $groups = null;

    public $customerGroup;
    /**
     * CustomerGroup constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Store\Model\System\Store $systemStore,
     * @param \Magetrend\AbandonedCart\Model\Config\Source\CustomerGroup $customerGroup
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Store\Model\System\Store $systemStore,
        \Magetrend\AbandonedCart\Model\Config\Source\CustomerGroup $customerGroup,
        array $data = []
    ) {
        $this->customerGroup = $customerGroup;
        parent::__construct($context, $systemStore, $data);
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $groupLabels = $this->customerGroup->toArray();
        $data = $row->getData($this->getColumn()->getIndex());

        if (empty($data) || strpos($data, ',-1,') !== false) {
            return $groupLabels[-1];
        }

        $data = explode(',', rtrim(trim($data, ','), ','));
        $html = '';
        foreach ($data as $item) {
            $html.= $groupLabels[$item].'<br/>';
        }

        return $html;
    }
}
