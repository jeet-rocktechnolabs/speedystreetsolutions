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

namespace Magetrend\AbandonedCart\Block\Adminhtml\Rule\Edit;

/**
 * Bckend Rule Edit Tabs Block
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    public $coreRegistry;

    /**
     * Tabs constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    // @codingStandardsIgnoreStart
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        // @codingStandardsIgnoreEnd
        $this->coreRegistry = $registry;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    /**
     * @return void
     */
    //@codingStandardsIgnoreLine
    protected function _construct()
    {
        parent::_construct();
        $this->setId('rule_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Rule Information'));
    }

    /**
     * @inheritdoc
     */
    //@codingStandardsIgnoreLine
    protected function _beforeToHtml()
    {
        $contentType = $this->_request->getParam('content_type');
        $id = $this->_request->getParam('id');
        $isNew = !$id?true:false;

        if (empty($contentType)) {
            $model = $this->coreRegistry->registry('current_rule');
            $contentType = $model->getType();
        }

        if ($contentType == \Magetrend\AbandonedCart\Model\Rule::TYPE_ABANDONED_CART) {
            $this->addCartGeneralTab();
            $this->addStoreCustomerTab();
            $this->addScheduleTab();
            $this->addConditionsTab();
            $this->addCouponTab();
        }

        if ($contentType == \Magetrend\AbandonedCart\Model\Rule::TYPE_FOLLOW_UP) {
            $this->addOrderGeneralTab();
            $this->addStoreCustomerTab();
            $this->addScheduleTab();
            $this->addConditionsTab();
            $this->addCouponTab();
        }

        if ($contentType == \Magetrend\AbandonedCart\Model\Rule::TYPE_BAR) {
            $this->addBarGeneralTab();
            $this->addStoreCustomerTab();
            $this->addBarSettingsTab();
        }

        return parent::_beforeToHtml();
    }

    public function addCartGeneralTab()
    {
        $this->addTab(
            'general_section',
            [
                'label' => __('General Settings'),
                'title' => __('General Settings'),
                'active' => true,
                'content' => $this->getLayout()->createBlock(
                    'Magetrend\AbandonedCart\Block\Adminhtml\Rule\Edit\Tab\Cart\General'
                )->toHtml()
            ]
        );
    }

    public function addOrderGeneralTab()
    {
        $this->addTab(
            'general_section',
            [
                'label' => __('General Settings'),
                'title' => __('General Settings'),
                'active' => true,
                'content' => $this->getLayout()->createBlock(
                    'Magetrend\AbandonedCart\Block\Adminhtml\Rule\Edit\Tab\Order\General'
                )->toHtml()
            ]
        );
    }

    public function addBarGeneralTab()
    {
        $this->addTab(
            'general_section',
            [
                'label' => __('General Settings'),
                'title' => __('General Settings'),
                'active' => true,
                'content' => $this->getLayout()->createBlock(
                    'Magetrend\AbandonedCart\Block\Adminhtml\Rule\Edit\Tab\Bar\General'
                )->toHtml()
            ]
        );
    }

    public function addScheduleTab()
    {
        $this->addTab(
            'field_section',
            [
                'label' => __('Schedule'),
                'title' => __('Schedule'),
                'content' => $this->getLayout()->createBlock(
                    'Magetrend\AbandonedCart\Block\Adminhtml\Rule\Edit\Tab\Schedule'
                )->toHtml()
            ]
        );
    }

    public function addConditionsTab()
    {
        $this->addTab(
            'condition_section',
            [
                'label' => __('Conditions'),
                'title' => __('Conditions'),
                'content' => $this->getLayout()->createBlock(
                    'Magetrend\AbandonedCart\Block\Adminhtml\Rule\Edit\Tab\Conditions'
                )->toHtml()
            ]
        );
    }

    public function addCouponTab()
    {
        $this->addTab(
            'coupon_section',
            [
                'label' => __('Discount Code Settings'),
                'title' => __('Discount Code Settings'),
                'content' => $this->getLayout()->createBlock(
                    'Magetrend\AbandonedCart\Block\Adminhtml\Rule\Edit\Tab\Coupon'
                )->toHtml()
            ]
        );
    }

    public function addEventsTab()
    {
        $this->addTab(
            'event_section',
            [
                'label' => __('Events / Triggers'),
                'title' => __('Events / Triggers'),
                'active' => false,
                'content' => $this->getLayout()->createBlock(
                    'Magetrend\AbandonedCart\Block\Adminhtml\Rule\Edit\Tab\Events'
                )->toHtml()
            ]
        );
    }

    public function addOrderEventTab()
    {
        $this->addTab(
            'event_section',
            [
                'label' => __('Events / Triggers'),
                'title' => __('Events / Triggers'),
                'active' => false,
                'content' => $this->getLayout()->createBlock(
                    'Magetrend\AbandonedCart\Block\Adminhtml\Rule\Edit\Tab\Order\Events'
                )->toHtml()
            ]
        );
    }

    public function addStoreCustomerTab()
    {
        $this->addTab(
            'store_customer_section',
            [
                'label' => __('Stores & Customer Groups'),
                'title' => __('Stores & Customer Groups'),
                'active' => false,
                'content' => $this->getLayout()->createBlock(
                    'Magetrend\AbandonedCart\Block\Adminhtml\Rule\Edit\Tab\StoreCustomer'
                )->toHtml()
            ]
        );
    }
    public function addBarSettingsTab()
    {
        $this->addTab(
            'bar_settings_section',
            [
                'label' => __('Bar Settings'),
                'title' => __('Bar Settings'),
                'active' => false,
                'content' => $this->getLayout()->createBlock(
                    'Magetrend\AbandonedCart\Block\Adminhtml\Rule\Edit\Tab\Bar\Settings'
                )->toHtml()
            ]
        );
    }
}
