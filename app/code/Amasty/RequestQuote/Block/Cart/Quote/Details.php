<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Cart\Quote;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Customer\Model\FormFactory as CustomerFormFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Details extends Template
{
    public const CUSTOMER_ACCOUNT_CREATE = 'customer_account_create';

    public const ADDITIONAL_ATTRIBUTES = [
        'prefix',
        'firstname',
        'middlename',
        'lastname',
        'suffix',
        'dob',
        'taxvat',
        'gender'
    ];

    /**
     * @var LayoutProcessorInterface[]
     */
    private $layoutProcessors;

    public function __construct(
        ?CustomerFormFactory $customerFormFactory, // @deprecated
        Context $context,
        array $layoutProcessors = [],
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->layoutProcessors = $layoutProcessors;
    }

    /**
     * @return string
     */
    public function getJsLayout()
    {
        if (isset($this->jsLayout['components']['details']['config'])) {
            foreach ($this->layoutProcessors as $layoutProcessor) {
                $this->jsLayout = $layoutProcessor->process($this->jsLayout);
            }
        }

        return json_encode($this->jsLayout, JSON_HEX_TAG);
    }
}
