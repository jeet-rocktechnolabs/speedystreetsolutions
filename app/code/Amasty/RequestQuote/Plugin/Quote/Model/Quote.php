<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Plugin\Quote\Model;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Amasty\RequestQuote\Model\Quote\Frontend\GetAmastyQuote;
use Amasty\RequestQuote\Model\QuoteRepository;
use Amasty\RequestQuote\Model\Source\Status;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote as QuoteModel;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\Quote\Address;

class Quote
{
    /**
     * @var QuoteRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var GetAmastyQuote
     */
    private $getAmastyQuote;

    public function __construct(
        QuoteRepositoryInterface $quoteRepository,
        State $appState,
        GetAmastyQuote $getAmastyQuote
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->appState = $appState;
        $this->getAmastyQuote = $getAmastyQuote;
    }

    /**
     * @return QuoteModel
     */
    public function aroundDelete(QuoteModel $subject, callable $proceed)
    {
        //prevent clearing amasty quotes
        if ($subject->getData(QuoteRepository::DELETE_FLAG)
            || !$this->quoteRepository->isAmastyQuote((int) $subject->getId())
        ) {
            $proceed();
        }

        return $subject;
    }

    /**
     * @return QuoteModel
     */
    public function aroundAssignCustomerWithAddressChange(
        QuoteModel $subject,
        callable $proceed,
        CustomerInterface $customer,
        ?Address $billingAddress = null,
        ?Address $shippingAddress = null
    ) {
        if (!$this->isFrontend() || !$this->isAddressConfigured($subject)) {
            $proceed($customer, $billingAddress, $shippingAddress);
        }

        return $subject;
    }

    /**
     * @param QuoteModel $subject
     * @param $proceed
     * @param AddressInterface|null $address
     * @return QuoteModel
     */
    public function aroundSetShippingAddress(QuoteModel $subject, $proceed, $address)
    {
        if (!$this->isFrontend() || !$this->isAddressCantBeModified($subject)) {
            $proceed($address);
        }

        return $subject;
    }

    /**
     * @param QuoteModel $subject
     * @param callable $proceed
     * @param int|string $addressId
     * @return QuoteModel
     */
    public function aroundRemoveAddress(QuoteModel $subject, callable $proceed, $addressId)
    {
        if (!$this->isFrontend() || !$this->isAddressCantBeModified($subject)) {
            $proceed($addressId);
        }

        return $subject;
    }

    /**
     * @return QuoteModel
     */
    public function aroundRemoveAllAddresses(QuoteModel $subject, callable $proceed)
    {
        if (!$this->isFrontend() || !$this->isAddressCantBeModified($subject)) {
            $proceed();
        }

        return $subject;
    }

    /**
     * @param QuoteModel $subject
     * @param callable $proceed
     * @param int|string $itemId
     * @return QuoteModel
     */
    public function aroundRemoveItem(QuoteModel $subject, callable $proceed, $itemId)
    {
        $item = $subject->getItemById($itemId);
        if (!$item || !$this->isFrontend() || $this->isItemCanBeDeleted($item)) {
            $proceed($itemId);
        }

        return $subject;
    }

    private function isItemCanBeDeleted(QuoteItem $item): bool
    {
        $result = true;
        $quoteId = (int) $item->getQuoteId();
        if (($this->isAmastyQuote($quoteId)
            && $this->getAmastyQuote($quoteId)->getData(QuoteInterface::STATUS) != Status::CREATED)
            || $item->getOptionByCode('amasty_quote_price')
        ) {
            $result = false;
        }

        return $result;
    }

    private function isAddressCantBeModified(QuoteModel $quote): bool
    {
        $result = false;
        if ($amastyQuote = $this->getAmastyQuote->execute($quote)) {
            $result = $amastyQuote->getData(QuoteInterface::SHIPPING_CONFIGURE)
                && !$amastyQuote->getData(QuoteInterface::SHIPPING_CAN_BE_MODIFIED);
        }

        return $result;
    }

    private function isAddressConfigured(QuoteModel $quote): bool
    {
        $result = false;
        if ($amastyQuote = $this->getAmastyQuote->execute($quote)) {
            $result = (bool) $amastyQuote->getData(QuoteInterface::SHIPPING_CONFIGURE);
        }

        return $result;
    }

    private function isAmastyQuote(int $quoteId): bool
    {
        return $this->quoteRepository->isAmastyQuote($quoteId);
    }

    private function getAmastyQuote(int $quoteId): QuoteInterface
    {
        return $this->quoteRepository->get($quoteId);
    }

    private function isFrontend(): bool
    {
        try {
            $result = in_array($this->appState->getAreaCode(), [
                Area::AREA_FRONTEND,
                Area::AREA_WEBAPI_REST,
                Area::AREA_WEBAPI_SOAP,
                Area::AREA_GRAPHQL
            ]);
        } catch (LocalizedException $e) {
            $result = false;
        }

        return $result;
    }
}
