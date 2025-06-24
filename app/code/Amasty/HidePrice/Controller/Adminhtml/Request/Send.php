<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Controller\Adminhtml\Request;

use Amasty\HidePrice\Model\Source\Status;
use Amasty\HidePrice\Model\Validator;
use Magento\Framework\App\ObjectManager;

class Send extends \Amasty\HidePrice\Controller\Adminhtml\Request
{
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var \Amasty\HidePrice\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Validator
     */
    private $validator;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Amasty\HidePrice\Model\RequestRepository $requestRepository,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Amasty\HidePrice\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Validator $validator = null // TODO move to not optional
    ) {
        parent::__construct($context, $requestRepository, $coreRegistry);
        $this->transportBuilder = $transportBuilder;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->validator = $validator ?? ObjectManager::getInstance()->get(Validator::class);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('request_id');

        $message = $this->getRequest()->getParam('email_text');
        if (!$this->validator->isNotEmpty(trim($message))) {
            $this->messageManager->addErrorMessage(__('Please enter a Email Text.'));
            $this->_redirect('*/*/edit', ['id' => $id]);
            return;
        }

        if ($id) {
            try {
                $model = $this->requestRepository->get($id);

                $emailTo = $model->getEmail();
                $sender = $this->helper->getModuleConfig('general/sender');
                $template = $this->helper->getModuleConfig('general/template');
                if ($this->sendEmail($model, $sender, $emailTo, $template, $message)) {
                    $model->setStatus(Status::ANSWERED);
                    $model->setMessageText($message);
                    $this->requestRepository->save($model);
                    $this->messageManager->addSuccessMessage(__('Email Answer was sent.'));
                }
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This request no longer exists.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Please select request id.'));
        }

        $this->_redirect('amasty_hideprice/*/');
    }

    /**
     * @param \Amasty\HidePrice\Model\Request $model
     * @param $sender
     * @param $emailTo
     * @param $template
     * @param $message
     *
     * @return bool
     */
    private function sendEmail(\Amasty\HidePrice\Model\Request $model, $sender, $emailTo, $template, $message)
    {
        try {
            $store = $this->storeManager->getStore($model->getStoreId());
            $data =  [
                'website_name'  => $store->getWebsite()->getName(),
                'group_name'    => $store->getGroup()->getName(),
                'store_name'    => $store->getName(),
                'store'         => $store,
                'request'       => $model,
                'message'       => $message,
                'customer_name' => $model->getName()
            ];

            $transport = $this->transportBuilder->setTemplateIdentifier(
                $template
            )->setTemplateOptions(
                ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $store->getId()]
            )->setTemplateVars(
                $data
            )->setFrom(
                $sender
            )->addTo(
                $emailTo,
                $model->getName()
            )->getTransport();

            $transport->sendMessage();
            return true;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return false;
        }
    }
}
