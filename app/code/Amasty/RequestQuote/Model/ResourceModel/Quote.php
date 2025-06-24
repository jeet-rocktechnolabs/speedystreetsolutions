<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\ResourceModel;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Model\Source\Status;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\SalesSequence\Model\Manager;

class Quote extends \Magento\Quote\Model\ResourceModel\Quote
{
    /**
     * Saved info about checking quotes for amasty quote implements
     * @var array
     */
    private $checkedQuotes = [];

    /**
     * @var EventManager
     */
    private $eventManager;

    public function __construct(
        Context $context,
        Snapshot $entitySnapshot,
        RelationComposite $entityRelationComposite,
        Manager $sequenceManager,
        $connectionName = null,
        EventManager $eventManager = null
    ) {
        parent::__construct($context, $entitySnapshot, $entityRelationComposite, $sequenceManager, $connectionName);
        // OM for backward compatibility
        $this->eventManager = $eventManager ?? ObjectManager::getInstance()->get(EventManager::class);
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param int $customerId
     * @return $this
     */
    public function loadByCustomerId($quote, $customerId)
    {
        $connection = $this->getConnection();
        $select = $this->_getLoadSelect(
            'customer_id',
            $customerId,
            $quote
        );
        $this->applyActiveFilter($select);

        $data = $connection->fetchRow($select);

        if ($data) {
            $quote->setData($data);
            $quote->setOrigData();
        }

        $this->_afterLoad($quote);

        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param int $quoteId
     * @return $this;
     */
    public function loadByIdWithoutStore($quote, $quoteId)
    {
        $connection = $this->getConnection();
        if ($connection) {
            $select = $this->_getLoadSelect('entity_id', $quoteId, $quote);

            $data = $connection->fetchRow($select);

            if ($data) {
                $quote->setData($data);
                $quote->setOrigData();
            }
        }

        $this->_afterLoad($quote);

        return $this;
    }

    /**
     * @param $quote
     * @param $quoteId
     */
    public function loadMagentoQuoteByIdWithoutStore($quote, $quoteId)
    {
        $connection = $this->getConnection();
        if ($connection) {
            $select = parent::_getLoadSelect('entity_id', $quoteId, $quote);

            $data = $connection->fetchRow($select);

            if ($data) {
                $quote->setData($data);
                $quote->setOrigData();
            }
        }

        $this->_afterLoad($quote);
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param int $quoteId
     * @return $this
     */
    public function loadActive($quote, $quoteId)
    {
        $connection = $this->getConnection();
        $select = $this->_getLoadSelect('entity_id', $quoteId, $quote)->where('is_active = ?', 1);
        $this->applyActiveFilter($select);

        $data = $connection->fetchRow($select);
        if ($data) {
            $quote->setData($data);
            $quote->setOrigData();
        }

        $this->_afterLoad($quote);

        return $this;
    }

    /**
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        $this->saveAmastyQuote($object);
        return parent::_afterSave($object);
    }

    /**
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterDelete(AbstractModel $object)
    {
        $this->deleteAmastyQuote($object);
        return parent::_afterDelete($object);
    }

    /**
     * @param AbstractModel $object
     */
    private function saveAmastyQuote(AbstractModel $object)
    {
        $shippingCanModified = $object->hasData(QuoteInterface::SHIPPING_CAN_BE_MODIFIED)
            ? (int) $object->getData(QuoteInterface::SHIPPING_CAN_BE_MODIFIED)
            : 1;
        $isShippingConfigured = $object->hasData(QuoteInterface::SHIPPING_CONFIGURE)
            ? (int) $object->getData(QuoteInterface::SHIPPING_CONFIGURE)
            : 0;

        $this->getConnection()->insertOnDuplicate($this->getAmastyQuoteTable(), [
            'quote_id'  => $object->getId(),
            'status'    => $object->getStatus(),
            'increment_id' => $object->prepareIncrementId(),
            'customer_name' => $object->prepareCustomerName(),
            'remarks' => $object->getRemarks(),
            'expired_date' => $object->getExpiredDate(),
            'reminder_date' => $object->getReminderDate(),
            QuoteInterface::SUBMITED_DATE => $object->getData(QuoteInterface::SUBMITED_DATE),
            QuoteInterface::ADMIN_NOTIFICATION_SEND => $object->getAdminNotificationSend(),
            QuoteInterface::DISCOUNT => $object->getData(QuoteInterface::DISCOUNT),
            QuoteInterface::SURCHARGE => $object->getData(QuoteInterface::SURCHARGE),
            QuoteInterface::REMINDER_SEND => $object->getData(QuoteInterface::REMINDER_SEND) ?: 0,
            QuoteInterface::SHIPPING_CAN_BE_MODIFIED => $shippingCanModified,
            QuoteInterface::SHIPPING_CONFIGURE => $isShippingConfigured,
            QuoteInterface::CUSTOM_FEE => (float) $object->getData(QuoteInterface::CUSTOM_FEE),
            QuoteInterface::CUSTOM_METHOD_ENABLED => (int) $object->getData(QuoteInterface::CUSTOM_METHOD_ENABLED),
            QuoteInterface::SUM_ORIGINAL_PRICE => $this->getSumOriginalPrice($object),
        ]);
    }

    private function deleteAmastyQuote(AbstractModel $object): void
    {
        $this->getConnection()->delete($this->getAmastyQuoteTable(), [
            'quote_id = ?' => (int)$object->getId(),
        ]);
    }

    /**
     * @param \Magento\Quote\Model\Quote $object
     * @return float
     */
    private function getSumOriginalPrice(AbstractModel $object): float
    {
        $origPrice= 0;
        foreach ($object->getAllVisibleItems() as $item) {
            if (!$item->isDeleted()) {
                $origPrice += $item->getBaseOriginalPrice() * $item->getQty();
            }
        }

        return $origPrice;
    }

    /**
     * @return string
     */
    public function getAmastyQuoteTable()
    {
        return $this->getTable('amasty_quote');
    }

    /**
     * @param \Zend_Db_Select $select
     */
    private function joinAmastyQuote($select)
    {
        $select->joinInner(
            ['amquote' => $this->getAmastyQuoteTable()],
            "amquote.quote_id = " . $this->getMainTable() . ".entity_id",
            [
                'status',
                'remarks',
                'increment_id',
                'customer_name',
                'expired_date',
                'reminder_date',
                'submited_date',
                QuoteInterface::ADMIN_NOTIFICATION_SEND,
                QuoteInterface::SURCHARGE,
                QuoteInterface::DISCOUNT,
                QuoteInterface::REMINDER_SEND,
                QuoteInterface::SHIPPING_CAN_BE_MODIFIED,
                QuoteInterface::SHIPPING_CONFIGURE,
                QuoteInterface::CUSTOM_FEE,
                QuoteInterface::CUSTOM_METHOD_ENABLED
            ]
        )
            ->order('updated_at ' . \Magento\Framework\DB\Select::SQL_DESC)
            ->limit(1);
    }

    /**
     * @param \Zend_Db_Select $select
     */
    private function applyActiveFilter($select)
    {
        $select->where('amquote.status =? ', Status::CREATED);
    }

    /**
     * @inheritdoc
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $this->joinAmastyQuote($select);

        return $select;
    }

    /**
     * @param AbstractModel $object
     *
     * @return $this
     */
    protected function processNotModifiedSave(AbstractModel $object)
    {
        // Amasty_AdminActionsLog compatibility.
        $this->eventManager->dispatch('amquote_not_modified_save_before', ['object' => $object]);

        $this->saveAmastyQuote($object);

        // Amasty_AdminActionsLog compatibility.
        $this->eventManager->dispatch('amquote_not_modified_save_after', ['object' => $object]);

        return parent::processNotModifiedSave($object);
    }

    /**
     * @param \Amasty\RequestQuote\Model\Quote $quote
     * @param $status
     * @return mixed
     */
    public function updateStatus(\Amasty\RequestQuote\Model\Quote $quote, $status)
    {
        $this->getConnection()->insertOnDuplicate($this->getAmastyQuoteTable(), [
            'quote_id'  => $quote->getId(),
            'status'    => $status
        ], ['status']);
        return $this;
    }

    /**
     * @param int $quoteId
     *
     * @return bool
     */
    public function isAmastyQuote($quoteId)
    {
        if (!isset($this->checkedQuotes[$quoteId])) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from($this->getAmastyQuoteTable())
                ->where('quote_id = ?', $quoteId);
            // detect amasty quote
            $this->checkedQuotes[$quoteId] = (bool) $connection->fetchRow($select);
        }

        return $this->checkedQuotes[$quoteId];
    }

    /**
     * @param \Amasty\RequestQuote\Model\Quote $quote
     * @param $remarks
     * @return mixed
     */
    public function updateRemarks(\Amasty\RequestQuote\Model\Quote $quote, $remarks)
    {
        $this->getConnection()->insertOnDuplicate($this->getAmastyQuoteTable(), [
            'quote_id' => $quote->getId(),
            'remarks' => $remarks
        ], ['remarks']);
        return $this;
    }

    /**
     * @param \Amasty\RequestQuote\Model\Quote $quote
     * @param array $data
     * @return mixed
     */
    public function updateData(\Amasty\RequestQuote\Model\Quote $quote, $data)
    {
        $this->getConnection()->insertOnDuplicate($this->getAmastyQuoteTable(), array_merge([
            'quote_id'  => $quote->getId()
        ], $data), array_keys($data));
        return $this;
    }
}
