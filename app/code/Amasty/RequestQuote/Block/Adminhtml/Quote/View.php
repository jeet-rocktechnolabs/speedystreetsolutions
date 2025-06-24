<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Adminhtml\Quote;

class View extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var string
     */
    protected $_blockGroup = 'Amasty_RequestQuote';

    /**
     * @var \Amasty\RequestQuote\Model\Quote\Backend\Session
     */
    private $quoteSession;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Amasty\RequestQuote\Model\Quote\Backend\Session $quoteSession,
        array $data = []
    ) {
        $this->quoteSession = $quoteSession;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_objectId = 'quote_id';
        $this->_controller = 'adminhtml_quote';
        $this->_mode = 'view';

        parent::_construct();

        $this->removeButton('delete');
        $this->removeButton('reset');
        $this->removeButton('save');
        $this->setId('amasty_quote_view');
        $quote = $this->getQuote();

        if (!$quote) {
            return;
        }

        if ($this->_isAllowedAction('Amasty_RequestQuote::close') && $quote->canClose()) {
            $this->addButton(
                'quote_close',
                [
                    'label' => __('Cancel / Close'),
                    'class' => 'quote-action-button',
                    'id' => 'quote-view-cancel-button',
                    'data_attribute' => [
                        'url' => $this->getCloseUrl()
                    ]
                ]
            );
        }

        if ($this->_isAllowedAction('Amasty_RequestQuote::approve') && $quote->canApprove()) {
            $this->addButton(
                'quote_approve',
                [
                    'label' => __('Approve'),
                    'class' => 'quote-action-button',
                    'id' => 'quote-view-approve-button',
                    'data_attribute' => [
                        'url' => $this->getApproveUrl(),
                        'amquote-js' => 'approve'
                    ]
                ]
            );
        }

        if ($this->_isAllowedAction('Amasty_RequestQuote::order')
            && $quote->canOrder()
            && !$quote->getCustomerIsGuest()
        ) {
            $this->addButton(
                'quote_order',
                [
                    'label' => __('Convert To Order'),
                    'class' => 'quote-action-button',
                    'id' => 'quote-view-quote-button',
                    'data_attribute' => [
                        'url' => $this->getOrderUrl()
                    ]
                ]
            );
        }

        if ($this->_isAllowedAction('Amasty_RequestQuote::pdfDownload')) {
            $this->addButton(
                'pdf_download',
                [
                    'label' => __('Download PDF'),
                    'class' => 'quote-action-button',
                    'id' => 'quote-view-pdf-button',
                    'data_attribute' => [
                        'url' => $this->getPdfDownloadUrl()
                    ]
                ]
            );
        }

        if ($this->_isAllowedAction('Amasty_RequestQuote::edit') && $quote->canEdit()) {
            $this->addButton(
                'quote_edit',
                [
                    'label' => __('Edit'),
                    'class' => 'quote-action-button primary',
                    'id' => 'quote-edit-button',
                    'data_attribute' => [
                        'url' => $this->getEditUrl()
                    ]
                ]
            );
        }
    }

    /**
     * @return \Amasty\RequestQuote\Api\Data\QuoteInterface
     */
    public function getQuote()
    {
        return $this->quoteSession->getQuote();
    }

    /**
     * @param string $params
     * @param array $params2
     * @return string
     */
    public function getUrl($params = '', $params2 = [])
    {
        $params2['quote_id'] = $this->getQuote()->getId();
        return parent::getUrl($params, $params2);
    }

    /**
     * @return string
     */
    public function getCloseUrl()
    {
        return $this->getUrl('amasty_quote/*/close');
    }

    /**
     * @return string
     */
    public function getPdfDownloadUrl()
    {
        return $this->getUrl('amasty_quote/*/pdf');
    }

    /**
     * @return string
     */
    public function getApproveUrl()
    {
        return $this->getUrl('amasty_quote/*/approve');
    }

    /**
     * @return string
     */
    public function getEditUrl()
    {
        return $this->getUrl('amasty_quote/quote_edit/start');
    }

    /**
     * @return string
     */
    public function getOrderUrl()
    {
        return $this->getUrl('amasty_quote/*/order');
    }

    /**
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->getQuote() && $this->getQuote()->getBackUrl()) {
            return $this->getQuote()->getBackUrl();
        }

        return $this->getUrl('amasty_quote/*/');
    }
}
