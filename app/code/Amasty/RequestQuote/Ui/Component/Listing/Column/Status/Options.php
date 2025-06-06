<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Ui\Component\Listing\Column\Status;

use Magento\Framework\Data\OptionSourceInterface;

class Options implements OptionSourceInterface
{
    /**
     * @var \Amasty\RequestQuote\Model\Source\Status
     */
    private $status;

    public function __construct(\Amasty\RequestQuote\Model\Source\Status $status)
    {
        $this->status = $status;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->status->getOptionArray(true);
    }
}
