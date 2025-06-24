<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magmodules\GoogleShopping\Block\Adminhtml\System\Config\Button;

use Exception;
use Magento\Backend\Block\Widget\Button;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magmodules\GoogleShopping\Api\Log\RepositoryInterface as LogRepository;

/**
 * Error log check button class
 */
class ErrorCheck extends Field
{

    /**
     * @var string
     */
    protected $_template = 'Magmodules_GoogleShopping::system/config/button/error.phtml';

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function _getElementHtml(AbstractElement $element): string
    {
        return $this->_toHtml();
    }

    /**
     * @return string
     */
    public function getErrorCheckUrl(): string
    {
        return $this->getUrl('googleshopping/log/stream', ['type' => 'error']);
    }

    /**
     * @return string
     */
    public function getButtonHtml(): string
    {
        try {
            return $this->getLayout()
                ->createBlock(Button::class)
                ->setData([
                    'id' => 'mm-ui-button_error',
                    'label' => __('Show last %1 error log records', LogRepository::STREAM_DEFAULT_LIMIT)
                ])->toHtml();
        } catch (Exception $e) {
            return '';
        }
    }
}
