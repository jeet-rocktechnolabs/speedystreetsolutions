<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model;

use Amasty\Base\Model\Serializer;
use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Api\Data\QuoteSearchResultsInterface;
use Amasty\RequestQuote\Api\Data\QuoteSearchResultsInterfaceFactory;
use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Amasty\RequestQuote\Model\Source\Status;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ObjectManager;

class QuoteRepository extends \Magento\Quote\Model\QuoteRepository implements QuoteRepositoryInterface
{
    public const DELETE_FLAG = 'amasty_quote_delete';

    /**
     * @var array
     */
    private $amastyColumns = [
        'quote_id',
        'customer_name',
        'increment_id',
        'status',
        'remarks',
        'expired_date',
        'reminder_date',
        'submited_date',
        'admin_notification_send'
    ];

    /**
     * @var Quote[]
     */
    protected $quotesById = [];

    /**
     * @var Quote[]
     */
    protected $magentoQuotesById = [];

    /**
     * @var Quote[]
     */
    protected $quotesByCustomerId = [];

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Amasty\RequestQuote\Model\QuoteRepository\SaveHandler
     */
    private $saveHandler;

    /**
     * @var \Amasty\RequestQuote\Model\QuoteRepository\LoadHandler
     */
    private $loadHandler;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var ResourceModel\Quote
     */
    private $quoteResource;

    /**
     * @var QuoteSearchResultsInterfaceFactory
     */
    private $quoteResultsDataFactory;

    public function __construct(
        QuoteFactory $quoteFactory,
        StoreManagerInterface $storeManager,
        \Amasty\RequestQuote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        CollectionProcessorInterface $collectionProcessor,
        \Magento\Quote\Api\Data\CartSearchResultsInterfaceFactory $searchResultsDataFactory,
        Serializer $serializer,
        \Amasty\RequestQuote\Model\ResourceModel\Quote $quoteResource,
        QuoteSearchResultsInterfaceFactory $quoteResultsDataFactory
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->storeManager = $storeManager;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsDataFactory = $searchResultsDataFactory;
        $this->serializer = $serializer;
        $this->quoteResource = $quoteResource;
        $this->quoteResultsDataFactory = $quoteResultsDataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function get($cartId, array $sharedStoreIds = [])
    {
        if (!isset($this->quotesById[$cartId])) {
            $quote = $this->loadQuote('loadByIdWithoutStore', 'cartId', $cartId, $sharedStoreIds);
            $this->getLoadHandler()->load($quote);
            $this->quotesById[$cartId] = $quote;
        }
        return $this->quotesById[$cartId];
    }

    /**
     * @param $cartId
     * @param array $sharedStoreIds
     * @return Quote
     */
    public function getMagentoQuote($cartId, array $sharedStoreIds = [])
    {
        if (!isset($this->magentoQuotesById[$cartId])) {
            $quote = $this->loadQuote('loadMagentoQuoteByIdWithoutStore', 'cartId', $cartId, $sharedStoreIds);
            $this->getLoadHandler()->load($quote);
            $this->magentoQuotesById[$cartId] = $quote;
        }
        return $this->magentoQuotesById[$cartId];
    }

    /**
     * {@inheritdoc}
     */
    public function getForCustomer($customerId, array $sharedStoreIds = [])
    {
        if (!isset($this->quotesByCustomerId[$customerId])) {
            $quote = $this->loadQuote('loadByCustomer', 'customerId', $customerId, $sharedStoreIds);
            $this->getLoadHandler()->load($quote);
            $this->quotesById[$quote->getId()] = $quote;
            $this->quotesByCustomerId[$customerId] = $quote;
        }
        return $this->quotesByCustomerId[$customerId];
    }

    /**
     * {@inheritdoc}
     */
    public function getActive($cartId, array $sharedStoreIds = [])
    {
        $quote = $this->get($cartId, $sharedStoreIds);
        if (!$quote->getIsActive()) {
            throw NoSuchEntityException::singleField('cartId', $cartId);
        }
        return $quote;
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveForCustomer($customerId, array $sharedStoreIds = [])
    {
        $quote = $this->getForCustomer($customerId, $sharedStoreIds);
        if (!$quote->getIsActive()) {
            throw NoSuchEntityException::singleField('customerId', $customerId);
        }
        return $quote;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        if ($quote->getId()) {
            $currentQuote = $this->get($quote->getId(), ['*']);

            foreach ($currentQuote->getData() as $key => $value) {
                if (!$quote->hasData($key)) {
                    $quote->setData($key, $value);
                }
            }
        }

        $this->getSaveHandler()->save($quote);
        unset($this->quotesById[$quote->getId()]);
        unset($this->quotesByCustomerId[$quote->getCustomerId()]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        $quoteId = $quote->getId();
        $customerId = $quote->getCustomerId();
        $quote->setData(self::DELETE_FLAG, true);
        $quote->delete();
        unset($this->quotesById[$quoteId]);
        unset($this->quotesByCustomerId[$customerId]);
    }

    /**
     * @param string $loadMethod
     * @param string $loadField
     * @param int $identifier
     * @param int[] $sharedStoreIds
     * @throws NoSuchEntityException
     * @return Quote
     */
    protected function loadQuote($loadMethod, $loadField, $identifier, array $sharedStoreIds = [])
    {
        /** @var Quote $quote */
        $quote = $this->quoteFactory->create();
        if ($sharedStoreIds) {
            $quote->setSharedStoreIds($sharedStoreIds);
        }
        $quote->$loadMethod($identifier);

        if (!$quote->getStoreId()) {
            $quote->setStoreId($this->storeManager->getStore()->getId());
        }

        if (!$quote->getId()) {
            throw NoSuchEntityException::singleField($loadField, $identifier);
        }
        return $quote;
    }

    /**
     * @return \Amasty\RequestQuote\Model\QuoteRepository\SaveHandler
     */
    private function getSaveHandler()
    {
        if (!$this->saveHandler) {
            $this->saveHandler = ObjectManager::getInstance()->get(
                \Amasty\RequestQuote\Model\QuoteRepository\SaveHandler::class
            );
        }
        return $this->saveHandler;
    }

    /**
     * @return \Amasty\RequestQuote\Model\QuoteRepository\LoadHandler
     */
    private function getLoadHandler()
    {
        if (!$this->loadHandler) {
            $this->loadHandler = ObjectManager::getInstance()->get(
                \Amasty\RequestQuote\Model\QuoteRepository\LoadHandler::class
            );
        }
        return $this->loadHandler;
    }

    /**
     * @param Quote $quote
     * @param $status
     * @return $this
     */
    public function updateStatus(\Amasty\RequestQuote\Model\Quote $quote, $status)
    {
        $this->getSaveHandler()->updateStatus($quote, $status);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function approve($quoteId)
    {
        $result = true;
        try {
            $this->updateStatus($this->get($quoteId, ['*']), Status::APPROVED);
        } catch (\Exception $e) {
            $result = false;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function expire($quoteId)
    {
        $result = true;
        try {
            $this->updateStatus($this->get($quoteId, ['*']), Status::EXPIRED);
        } catch (\Exception $e) {
            $result = false;
        }

        return $result;
    }

    public function getRequestsList(SearchCriteriaInterface $searchCriteria): QuoteSearchResultsInterface
    {
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if (in_array($filter->getField(), $this->amastyColumns)) {
                    $filter->setField('amasty_quote.' . $filter->getField());
                }
            }
        }

        $this->quoteCollection = $this->quoteCollectionFactory->create();
        /** @var QuoteSearchResultsInterface $searchData */
        $searchData = $this->quoteResultsDataFactory->create();
        $searchData->setSearchCriteria($searchCriteria);

        $this->collectionProcessor->process($searchCriteria, $this->quoteCollection);
        $this->extensionAttributesJoinProcessor->process($this->quoteCollection);
        foreach ($this->quoteCollection->getItems() as $quote) {
            /** @var Quote $quote */
            $this->getLoadHandler()->load($quote);
        }
        $searchData->setItems($this->quoteCollection->getItems());
        $searchData->setTotalCount($this->quoteCollection->getSize());
        return $searchData;
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomerNote($quoteId, $note)
    {
        return $this->updateRemark($quoteId, QuoteInterface::CUSTOMER_NOTE_KEY, $note);
    }

    /**
     * {@inheritdoc}
     */
    public function addAdminNote($quoteId, $note)
    {
        return $this->updateRemark($quoteId, QuoteInterface::ADMIN_NOTE_KEY, $note);
    }

    /**
     * @param int $quoteId
     * @param string $key
     * @param string $note
     *
     * @return bool
     */
    private function updateRemark($quoteId, $key, $note)
    {
        $result = true;
        try {
            /** @var Quote $quote */
            $quote = $this->get($quoteId, ['*']);
            $remarks = $quote->getRemarks()
                ? $this->serializer->unserialize($quote->getRemarks())
                : [];
            $remarks[$key] = $note;
            $this->getSaveHandler()->updateRemarks($quote, $this->serializer->serialize($remarks));
        } catch (\Exception $e) {
            $result = false;
        }

        return $result;
    }

    /**
     * @param Quote $quote
     * @param $data
     * @return $this
     */
    public function updateData(\Amasty\RequestQuote\Model\Quote $quote, $data)
    {
        $this->getSaveHandler()->updateData($quote, $data);
        return $this;
    }

    /**
     * @param int $quoteId
     * @return bool
     */
    public function isAmastyQuote(int $quoteId): bool
    {
        return $this->quoteResource->isAmastyQuote($quoteId);
    }
}
