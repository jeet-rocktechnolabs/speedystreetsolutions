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

namespace Magetrend\AbandonedCart\Model;

use Magento\Framework\Setup;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Sample data manager model
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class SampleDataManager
{
    /**
     * @var \Magento\Framework\App\State|Setup\SampleData\State
     */
    public $state;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    public $resourceConfig;

    /**
     * @var \Magetrend\AbandonedCart\Model\RuleFactory
     */
    public $ruleFactory;

    /**
     * @var \Magetrend\AbandonedCart\Model\QueueFactory
     */
    public $queueFactory;

    /**
     * @var \Magetrend\AbandonedCart\Model\ScheduleFactory
     */
    public $scheduleFactory;

    /**
     * @var \Magetrend\AbandonedCart\Model\ResourceModel\Rule\CollectionFactory
     */
    public $ruleCollectionFactory;

    /**
     * @var \Magetrend\AbandonedCart\Model\ResourceModel\Queue\CollectionFactory
     */
    public $queuecollectionFactory;

    /**
     * @var \Magetrend\AbandonedCart\Model\ResourceModel\Schedule\CollectionFactory
     */
    public $scheduleCollectionFactory;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    public $salesRuleFactory;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory
     */
    public $salesRuleCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \Magento\Customer\Model\Config\Source\Group
     */
    public $customerGroups;

    /**
     * @var array
     */
    private $rule = [];

    /**
     * @var array
     */
    private $email = [];

    /**
     * @var array
     */
    private $salesRule = [];

    public $emailTemplateFactory;

    public $emailTemplateCollectionFactory;

    /**
     * InstallData constructor.
     * @param \Magento\Framework\App\State $state
     * @param \Magetrend\AbandonedCart\Model\RuleFactory $ruleFactory
     * @param \Magetrend\AbandonedCart\Model\QueueFactory $queueFactory
     * @param \Magetrend\AbandonedCart\Model\ScheduleFactory $scheduleFactory
     * @param \Magetrend\AbandonedCart\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory
     * @param \Magetrend\AbandonedCart\Model\ResourceModel\Queue\CollectionFactory $queuecollectionFactory
     * @param \Magetrend\AbandonedCart\Model\ResourceModel\Schedule\CollectionFactory $scheduleCollectionFactory
     * @param \Magento\SalesRule\Model\RuleFactory $salesRuleFactory
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $salesRuleCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Config\Source\Group $customerGroups,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magetrend\AbandonedCart\Model\RuleFactory $ruleFactory,
        \Magetrend\AbandonedCart\Model\QueueFactory $queueFactory,
        \Magetrend\AbandonedCart\Model\ScheduleFactory $scheduleFactory,
        \Magetrend\AbandonedCart\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        \Magetrend\AbandonedCart\Model\ResourceModel\Queue\CollectionFactory $queuecollectionFactory,
        \Magetrend\AbandonedCart\Model\ResourceModel\Schedule\CollectionFactory $scheduleCollectionFactory,
        \Magento\SalesRule\Model\RuleFactory $salesRuleFactory,
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $salesRuleCollectionFactory,
        \Magento\Email\Model\TemplateFactory $emailTemplateFactory,
        \Magento\Email\Model\ResourceModel\Template\CollectionFactory $emailTemplateCollectionFactory
    ) {
        $this->state = $state;
        $this->resourceConfig = $resourceConfig;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->queuecollectionFactory = $queuecollectionFactory;
        $this->ruleFactory = $ruleFactory;
        $this->scheduleFactory = $scheduleFactory;
        $this->queueFactory = $queueFactory;
        $this->salesRuleCollectionFactory = $salesRuleCollectionFactory;
        $this->salesRuleFactory = $salesRuleFactory;
        $this->storeManager = $storeManager;
        $this->customerGroups = $customerGroups;
        $this->emailTemplateFactory = $emailTemplateFactory;
        $this->emailTemplateCollectionFactory = $emailTemplateCollectionFactory;
    }

    /**
     * Load sample data
     */
    public function loadSampleData()
    {
        $this->clean();
        $this->createRules();
        $this->createEmailTemplate('magetrend_abandoned_cart_email_1', 'MT Abandoned Cart (1)');
        $this->createEmailTemplate('magetrend_abandoned_cart_email_2', 'MT Abandoned Cart (2)');
        $this->createEmailTemplate('magetrend_abandoned_cart_email_3', 'MT Abandoned Cart (3)');
        $this->createSalesRules();
        $this->createSchedules();
        $this->updateConfig();
    }

    /**
     * Clean data
     */
    public function clean()
    {
        $this->ruleCollectionFactory->create()
            ->addFieldToFilter('name', ['like' => 'SampleData: Default'])
            ->walk('delete');

        $this->salesRuleCollectionFactory->create()
            ->addFieldToFilter('name', 'abandoned_cart_demo_rule')
            ->walk('delete');

        $this->emailTemplateCollectionFactory->create()
            ->addFieldToFilter('template_code', ['like' => 'MT Abandoned Cart (%'])
            ->walk('delete');
    }

    /**
     * Create demo reles
     */
    public function createRules()
    {
        $this->rule[] = $this->ruleFactory->create()
            ->setData([
                'is_active' => 1,
                'name' => 'SampleData: Default',
                'type' => \Magetrend\AbandonedCart\Model\Rule::TYPE_BAR,
                'store_ids' => ',0,',
                'customer_groups' => ',-1,',
                'trigger_events' => ',time_out,',
                'cancel_events' => ',,',
                'payment_methods' => ',,',
                'priority' => 0,
                'color_1' => '#edf058',
                'color_2' => '#000000',
                'font_size_1' => '24px',
                'font_1' => 'Arial',
                'bar_text' => 'Some of your items you added to cart are almost sold out. Hurry up!',
                'item_qty' => 0,
                'show_after' => 0,
                'hide_after' => 7,
                'delay_time' => 0,
            ])->save();

        $this->rule[] = $this->ruleFactory->create()
            ->setData([
                'is_active' => 1,
                'name' => 'SampleData: Default',
                'type' => \Magetrend\AbandonedCart\Model\Rule::TYPE_ABANDONED_CART,
                'store_ids' => ',0,',
                'customer_groups' => ',-1,',
                'trigger_events' => ',,',
                'cancel_events'
                => ',new_cart_was_created,new_order_was_placed,link_clicked,out_of_stock,out_of_stock_all,',
                'payment_methods' => ',,',
                'priority' => 0,
                'coupon_expire_in_days' => '2',
                'coupon_length' => 8,
                'coupon_format' => 'alphanum',
                'coupon_prefix' => 'AC-',
                'coupon_suffix' => '',
                'coupon_dash' => '4',
                'item_qty' => 0,
                'show_after' => 0,
                'delay_time' => 0,
            ])->save();

        $this->rule[] = $this->ruleFactory->create()
            ->setData([
                'is_active' => 1,
                'name' => 'SampleData: Default',
                'type' => \Magetrend\AbandonedCart\Model\Rule::TYPE_FOLLOW_UP,
                'store_ids' => ',0,',
                'customer_groups' => ',-1,',
                'trigger_events' => ',not_paid,',
                'cancel_events' => ',new_cart_was_created,new_order_was_placed,link_clicked,out_of_stock,out_of_stock_all,order_was_paid,',
                'payment_methods' => ',checkmo,',
                'priority' => 0,
                'item_qty' => 0,
                'show_after' => 0,
                'delay_time' => 0,
            ])->save();
    }

    public function createEmailTemplate($templateId, $name)
    {
        $this->email[] = $this->emailTemplateFactory->create()
            ->setForcedArea($templateId)
            ->loadDefault($templateId)
            ->setId(null)
            ->setOrigTemplateCode($templateId)
            ->setTemplateCode($name)
            ->save();
    }

    /**
     * Create discount rule
     */
    public function createSalesRules()
    {
        $websites = $this->storeManager->getWebsites();
        $websiteIds = [];
        foreach ($websites as $website) {
            $websiteIds[] = $website->getId();
        }

        $customerGroupIds = [0];
        $customerGroups = $this->customerGroups->toOptionArray();
        foreach ($customerGroups as $group) {
            $customerGroupIds[] = $group['value'];
        }

        $this->salesRule[] = $this->salesRuleFactory->create()
            ->setData([
                'name' => 'abandoned_cart_demo_rule',
                'description' => 'Abandoned Cart Demo Rule (1% discount)',
                'uses_per_customer' => 1,
                'is_active' => 1,
                'simple_action' => 'by_percent',
                'discount_amount' => 1,
                'coupon_type' => 2,
                'use_auto_generation' => 1,
                'uses_per_coupon' => 1,
                'is_advanced' => 1,
            ])
            ->setWebsiteIds($websiteIds)
            ->setCustomerGroupIds($customerGroupIds)
            ->save();
    }

    /**
     * Create schedules
     */
    public function createSchedules()
    {
        $rule = $this->rule[1];
        $salesRule = $this->salesRule[0];
        $template = $this->email[0];
        $this->scheduleFactory->create()
            ->setData([
                'rule_id' => $rule->getId(),
                'sales_rule_id' => $salesRule->getId(),
                'sort_order' => 0,
                'email_template' => $template->getId(),
                'delay_day' => '0',
                'delay_hour' => '0',
                'delay_minute' => 30,
            ])->save();

        $template = $this->email[1];
        $this->scheduleFactory->create()
            ->setData([
                'rule_id' => $rule->getId(),
                'sales_rule_id' => $salesRule->getId(),
                'sort_order' => 0,
                'email_template' => $template->getId(),
                'delay_day' => 1,
                'delay_hour' => 0,
                'delay_minute' => 0,
            ])->save();

        $template = $this->email[2];
        $this->scheduleFactory->create()
            ->setData([
                'rule_id' => $rule->getId(),
                'sales_rule_id' => $salesRule->getId(),
                'sort_order' => 0,
                'email_template' => $template->getId(),
                'delay_day' => 2,
                'delay_hour' => 0,
                'delay_minute' => 0,
            ])->save();

        $rule = $this->rule[2];
        $this->scheduleFactory->create()
            ->setData([
                'rule_id' => $rule->getId(),
                'sales_rule_id' => 0,
                'sort_order' => 0,
                'email_template' => 'magetrend_follow_up_email',
                'delay_day' => '0',
                'delay_hour' => '0',
                'delay_minute' => 30,
            ])->save();

        $this->scheduleFactory->create()
            ->setData([
                'rule_id' => $rule->getId(),
                'sales_rule_id' => 0,
                'sort_order' => 0,
                'email_template' => 'magetrend_follow_up_email',
                'delay_day' => 1,
                'delay_hour' => 0,
                'delay_minute' => 0,
            ])->save();

        $this->scheduleFactory->create()
            ->setData([
                'rule_id' => $rule->getId(),
                'sales_rule_id' => 0,
                'sort_order' => 0,
                'email_template' => 'magetrend_follow_up_email',
                'delay_day' => 3,
                'delay_hour' => 0,
                'delay_minute' => 0,
            ])->save();
    }

    public function updateConfig()
    {
        $this->resourceConfig
            ->saveConfig('abandonedcart/general/is_active', 0, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        $this->resourceConfig
            ->saveConfig('abandonedcart/cron/limit', 1, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        $this->resourceConfig
            ->saveConfig('abandonedcart/email/from', 'ident_general', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
    }
}
