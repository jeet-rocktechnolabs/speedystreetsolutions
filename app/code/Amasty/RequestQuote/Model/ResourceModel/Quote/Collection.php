<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\ResourceModel\Quote;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Model\Source\Status;

class Collection extends \Magento\Quote\Model\ResourceModel\Quote\Collection
{
    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init(\Amasty\RequestQuote\Model\Quote::class, \Amasty\RequestQuote\Model\ResourceModel\Quote::class);
    }

    /**
     * @inheritdoc
     */
    protected function _renderFiltersBefore()
    {
        $this->getSelect()->join(
            ['amasty_quote' => $this->getResource()->getAmastyQuoteTable()],
            'amasty_quote.quote_id = main_table.entity_id',
            [
                'status',
                'remarks',
                'increment_id',
                'customer_name',
                'expired_date',
                'reminder_date',
                'submited_date',
                QuoteInterface::DISCOUNT,
                QuoteInterface::SURCHARGE
            ]
        );

        parent::_renderFiltersBefore();
    }

    /**
     * @return $this
     */
    public function getProposalCollection()
    {
        $this->addFieldToFilter('amasty_quote.status', Status::APPROVED);
        $dateCondition = 'DATE_FORMAT(%s, \'%%Y-%%m-%%d %%H:%%i\') <= DATE_FORMAT(NOW(), \'%%Y-%%m-%%d %%H:%%i\')';
        $this->getSelect()
            ->columns([
                'need_expired_send' => $this->getConnection()->getCheckSql(
                    sprintf($dateCondition, 'expired_date'),
                    1,
                    0
                ),
                'need_reminder_send' => $this->getConnection()->getCheckSql(
                    sprintf($dateCondition, 'reminder_date'),
                    1,
                    0
                )
            ])
            ->where(sprintf($dateCondition, 'expired_date') . ' OR '
                . sprintf($dateCondition . ' AND reminder_send != 1', 'reminder_date'));

        return $this;
    }
}
