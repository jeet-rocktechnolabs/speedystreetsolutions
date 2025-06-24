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

namespace Magetrend\AbandonedCart\Block\Adminhtml\Widget\Grid\Column\Filter;

/**
 * Grid column filter store block class
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Store extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Store
{
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

    /**
     * Render HTML of the element
     *
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getHtml()
    {
        $websiteCollection = $this->_systemStore->getWebsiteCollection();
        $groupCollection = $this->_systemStore->getGroupCollection();
        $storeCollection = $this->_systemStore->getStoreCollection();

        $allShow = $this->getColumn()->getStoreAll();

        $html = '<select class="admin__control-select" name="' . $this->escapeHtml(
                $this->_getHtmlName()
            ) . '" ' . $this->getColumn()->getValidateClass() . $this->getUiId(
                'filter',
                $this->_getHtmlName()
            ) . '>';
        $value = $this->getColumn()->getValue();

        $html .= '<option value=""'
            . ($value == self::ALL_STORE_VIEWS ? ' selected="selected"' : '') . '>'
            . '</option>';

        $html .= '<option value="' . self::ALL_STORE_VIEWS . '"'
            . ($value == self::ALL_STORE_VIEWS ? ' selected="selected"' : '') . '>'
            . __('All Store Views') . '</option>';

        foreach ($websiteCollection as $website) {
            $websiteShow = false;
            foreach ($groupCollection as $group) {
                if ($group->getWebsiteId() != $website->getId()) {
                    continue;
                }
                $groupShow = false;
                foreach ($storeCollection as $store) {
                    if ($store->getGroupId() != $group->getId()) {
                        continue;
                    }
                    if (!$websiteShow) {
                        $websiteShow = true;
                        $html .= '<optgroup label="' . $this->escapeHtml($website->getName()) . '"></optgroup>';
                    }
                    if (!$groupShow) {
                        $groupShow = true;
                        $html .= '<optgroup label="&nbsp;&nbsp;&nbsp;&nbsp;' . $this->escapeHtml(
                                $group->getName()
                            ) . '">';
                    }
                    $value = $this->getValue();
                    $selected = $value == $store->getId() ? ' selected="selected"' : '';
                    $html .= '<option value="' .
                        $store->getId() .
                        '"' .
                        $selected .
                        '>&nbsp;&nbsp;&nbsp;&nbsp;' .
                        $this->escapeHtml(
                            $store->getName()
                        ) . '</option>';
                }
                if ($groupShow) {
                    $html .= '</optgroup>';
                }
            }
        }
        if ($this->getColumn()->getDisplayDeleted()) {
            $selected = $this->getValue() == '_deleted_' ? ' selected' : '';
            $html .= '<option value="_deleted_"' . $selected . '>' . __('[ deleted ]') . '</option>';
        }
        $html .= '</select>';
        return $html;
    }
}
