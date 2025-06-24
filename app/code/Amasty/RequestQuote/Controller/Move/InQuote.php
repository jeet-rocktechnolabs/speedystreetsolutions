<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Controller\Move;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;

class InQuote extends AbstractMove
{
    /**
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $currentQuote = $this->getSession('checkout')->getQuote();

        if ($currentQuote->getId() &&
            !$this->getAmastyQuoteResolver()->execute($currentQuote)
        ) {
            $mergeResult = $this->swapQuote($this->getQuote(), $currentQuote, false, true);
            foreach ($mergeResult->getWarnings() as $warning) {
                $this->messageManager->addNoticeMessage($warning);
            }
            if ($mergeResult->getResult()) {
                $result->setUrl($this->urlResolver->getCartUrl());
            } else {
                $result->setUrl($this->_url->getUrl('checkout/cart'));
            }
        } else {
            $this->messageManager->addErrorMessage(
                __('This is approved quote.')
            );
            $result->setUrl($this->_url->getUrl('checkout/cart'));
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    protected function getType()
    {
        return 'request';
    }
}
