<?php
namespace Rocktechnolabs\Custom\Plugin;

class OrderGridCollectionPlugin
{
    public function beforeLoad(\Magento\Sales\Model\ResourceModel\Order\Grid\Collection $subject, $printQuery = false, $logQuery = false)
    {
         $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom-ddd.log');
$logger = new \Zend_Log();
$logger->addWriter($writer);
$logger->info('text message');
        // Join billing address to get telephone
        $subject->getSelect()->joinLeft(
            ['billing_address' => $subject->getTable('sales_order_address')],
            'main_table.entity_id = billing_address.parent_id AND billing_address.address_type = "billing"',
            ['telephone']
        );

        return [$printQuery, $logQuery];
    }
}
