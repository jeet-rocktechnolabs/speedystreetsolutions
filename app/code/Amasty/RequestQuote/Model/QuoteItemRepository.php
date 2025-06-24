<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model;

use Amasty\Base\Model\Serializer;
use Amasty\RequestQuote\Api\Data\QuoteItemInterface;
use Amasty\RequestQuote\Api\QuoteItemRepositoryInterface;
use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Amasty\RequestQuote\Model\Quote\Item\Query\GetListForQuoteInterface;
use Amasty\RequestQuote\Model\ResourceModel\Quote\Item as ItemResource;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\ItemFactory;

class QuoteItemRepository implements QuoteItemRepositoryInterface
{
    /**
     * @var ItemFactory
     */
    private $itemFactory;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var ItemResource
     */
    private $itemResource;

    /**
     * @var QuoteRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var GetListForQuoteInterface
     */
    private $getListForQuote;

    public function __construct(
        ItemFactory $itemFactory,
        Serializer $serializer,
        ItemResource $itemResource,
        ?QuoteRepositoryInterface $quoteRepository = null,
        ?ConfigProvider $configProvider = null,
        ?GetListForQuoteInterface $getListForQuote = null
    ) {
        $this->itemFactory = $itemFactory;
        $this->serializer = $serializer;
        $this->itemResource = $itemResource;
        $this->quoteRepository = $quoteRepository ?? ObjectManager::getInstance()->get(QuoteRepositoryInterface::class);
        $this->configProvider = $configProvider ?? ObjectManager::getInstance()->get(ConfigProvider::class);
        $this->getListForQuote = $getListForQuote ?? ObjectManager::getInstance()->get(GetListForQuoteInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomerNote($quoteItemId, $note)
    {
        return $this->updateAdditinalData($quoteItemId, QuoteItemInterface::CUSTOMER_NOTE_KEY, $note);
    }

    /**
     * {@inheritdoc}
     */
    public function addAdminNote($quoteItemId, $note)
    {
        return $this->updateAdditinalData($quoteItemId, QuoteItemInterface::ADMIN_NOTE_KEY, $note);
    }

    /**
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $quoteId, int $itemId): bool
    {
        if (!$this->isEnabled()) {
            throw new StateException(__('Quote Cart has been disabled.'));
        }

        /** @var Quote $quote */
        $quote = $this->quoteRepository->get($quoteId);
        $quoteItem = $quote->getItemById($itemId);
        if (!$quoteItem) {
            throw new NoSuchEntityException(
                __('The %1 Quote Cart doesn\'t contain the %2 item.', $quoteId, $itemId)
            );
        }
        try {
            $quote->removeItem($itemId);
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__("The item couldn't be removed from the quote."));
        }

        return true;
    }

    /**
     * @param int $quoteItemId
     * @param string $key
     * @param string $note
     *
     * @return bool
     */
    private function updateAdditinalData($quoteItemId, $key, $note)
    {
        $result = true;
        try {
            /** @var Item $quote */
            $item = $this->itemFactory->create()
                ->load($quoteItemId);
            $additinalData = $item->getAdditionalData()
                ? $this->serializer->unserialize($item->getAdditionalData())
                : [];
            $additinalData[$key] = $note;
            $this->itemResource->updateAdditinalData(
                $item->getId(),
                $this->serializer->serialize($additinalData)
            );
        } catch (\Exception $e) {
            $result = false;
        }

        return $result;
    }

    /**
     * @return QuoteItemInterface[]
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function getList(int $quoteId): array
    {
        if (!$this->isEnabled()) {
            throw new StateException(__('Quote Cart has been disabled.'));
        }

        $quote = $this->quoteRepository->get($quoteId);
        if (!$this->isCustomerGroupAllowed((int)$quote->getCustomer()->getGroupId())) {
            throw new StateException(__('Quote Cart has been disabled for this customer group.'));
        }

        return $this->getListForQuote->execute($quoteId);
    }

    private function isEnabled(): bool
    {
        return $this->configProvider->isActive();
    }

    private function isCustomerGroupAllowed(int $customerGroupId): bool
    {
        return in_array($customerGroupId, $this->configProvider->getAllowedCustomerGroups());
    }
}
