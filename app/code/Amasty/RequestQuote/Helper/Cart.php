<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Helper;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Model\UrlResolver;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Cart extends AbstractHelper
{
    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializer;

    /**
     * @var UrlResolver
     */
    private $urlResolver;

    public function __construct(
        Context $context,
        \Magento\Framework\Escaper $escaper,
        \Amasty\Base\Model\Serializer $serializer,
        UrlResolver $urlResolver
    ) {
        parent::__construct($context);
        $this->escaper = $escaper;
        $this->serializer = $serializer;
        $this->urlResolver = $urlResolver;
    }

    /**
     * @return \Magento\Framework\Escaper
     */
    public function getEscaper()
    {
        return $this->escaper;
    }

    /**
     * @return string
     */
    public function getCartUrl()
    {
        return $this->urlResolver->getCartUrl();
    }

    /**
     * @param $product
     * @param array $additional
     * @return string
     */
    public function getAddUrl($product, $additional = [])
    {
        if (isset($additional['useUencPlaceholder'])) {
            $uenc = "%uenc%";
            unset($additional['useUencPlaceholder']);
        } else {
            $uenc = $this->urlEncoder->encode($this->_urlBuilder->getCurrentUrl());
        }

        $urlParamName = \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED;

        $routeParams = [
            $urlParamName => $uenc,
            'product' => $product ? $product->getEntityId() : null,
            '_secure' => $this->_getRequest()->isSecure()
        ];

        if (!empty($additional)) {
            $routeParams = array_merge($routeParams, $additional);
        }

        if ($product && $product->hasUrlDataObject()) {
            $routeParams['_scope'] = $product->getUrlDataObject()->getStoreId();
            $routeParams['_scope_to_url'] = true;
        }

        if ($this->_getRequest()->getRouteName() == 'amasty_quote'
            && $this->_getRequest()->getControllerName() == 'cart'
        ) {
            $routeParams['in_cart'] = 1;
        }

        return $this->_getUrl('amasty_quote/cart/add', $routeParams);
    }

    /**
     * @param $note
     * @return string
     */
    public function prepareCustomerNoteForSave($note)
    {
        return $note ? $this->serializer->serialize(['customer_note' => trim($note)]) : '';
    }

    /**
     * @param array $additionalData
     * @param array $updateDate
     * @return bool|string
     */
    public function updateAdditionalData($additionalData, $updateDate)
    {
        $additionalData = $this->serializer->unserialize($additionalData) ?: [];
        $additionalData = array_merge($additionalData, $updateDate);
        return $this->serializer->serialize($additionalData);
    }

    /**
     * @param string $additionalData
     *
     * @return string
     */
    public function retrieveCustomerNote($additionalData)
    {
        $customerNote = '';
        $additionalData = $this->serializer->unserialize($additionalData);
        if (isset($additionalData[QuoteInterface::CUSTOMER_NOTE_KEY])) {
            $customerNote = $additionalData[QuoteInterface::CUSTOMER_NOTE_KEY];
        }

        return $customerNote;
    }
}
