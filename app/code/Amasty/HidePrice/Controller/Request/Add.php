<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Controller\Request;

use Amasty\HidePrice\Model\Request;
use Amasty\HidePrice\Model\Validator;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;

class Add extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    /**
     * @var \Amasty\HidePrice\Model\RequestFactory
     */
    private $requestFactory;

    /**
     * @var \Amasty\HidePrice\Model\RequestRepository
     */
    private $requestRepository;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Amasty\HidePrice\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\Data\Form\Filter\Escapehtml
     */
    private $escapehtml;

    /**
     * @var Validator
     */
    private $validator;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Amasty\HidePrice\Model\RequestFactory $requestFactory,
        \Amasty\HidePrice\Model\RequestRepository $requestRepository,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Amasty\HidePrice\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Form\Filter\Escapehtml $escapehtml,
        Validator $validator = null // TODO move to not optional
    ) {
        parent::__construct($context);
        $this->formKeyValidator = $formKeyValidator;
        $this->requestFactory = $requestFactory;
        $this->requestRepository = $requestRepository;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->jsonEncoder = $jsonEncoder;
        $this->helper = $helper;
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
        $this->escapehtml = $escapehtml;
        $this->validator = $validator ?? ObjectManager::getInstance()->get(Validator::class);
    }

    public function execute()
    {
        $message = [
            'error' => __(
                'Sorry. There is a problem with Your Quote Request.' .
                ' Please try again or use Contact Us link in the menu.'
            )
        ];
        if ($this->getRequest()->isPost()) {
            try {
                $data = $this->getValidData();

                /** @var  Request $model */
                $model = $this->requestFactory->create();
                $model->addData($data);
                $this->requestRepository->save($model);
                $message = ['success' => __('Thanks for contacting us. We\'ll respond to you as soon as possible. ')];

                $this->sendAdminNotification($model);
                $this->sendAutoReply($model);

            } catch (LocalizedException $e) {
                $message = ['error' => $e->getMessage()];
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
        $this->getResponse()->representJson(
            $this->jsonEncoder->encode($message)
        );
    }

    /**
     * Validates all data
     *
     * @throws LocalizedException
     * @return array
     */
    private function getValidData()
    {
        $data = $this->getRequest()->getPostValue();

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            throw new LocalizedException(
                __('Form key is not valid. Please try to reload the page.')
            );
        }

        if (!$this->validator->isValidEmail($data['email'])) {
            throw new LocalizedException(__('Please enter a valid email address.'));
        }

        $data['name'] = $this->escapehtml->outputFilter(trim($data['name']));
        if (!$this->validator->isNotEmpty($data['name'])) {
            throw new LocalizedException(__('Please enter a name.'));
        }

        $data['phone'] = $this->escapehtml->outputFilter(trim($data['phone']));
        if (!$this->validator->isNotEmpty($data['phone'])) {
            throw new LocalizedException(__('Please enter a phone.'));
        }

        $data['product_id'] = (int)$data['product_id'];
        if (!$this->validator->isNotEmpty($data['product_id'])
           || !($product = $this->productRepository->getById($data['product_id']))
        ) {
            throw new LocalizedException(__('There are no product for your request.'));
        } else {
            $data['product_name'] = $product->getName();
        }

        if (array_key_exists('comment', $data)) {
            $data['comment'] = $this->escapehtml->outputFilter(trim($data['comment']));
        }

        if ($this->helper->isGDPREnabled() && (!isset($data['gdpr']) || !$data['gdpr'])) {
            throw new LocalizedException(__('Please agree to the Privacy Policy'));
        }

        $data['store_id'] = $this->storeManager->getStore()->getId();

        return $data;
    }

    /**
     * @param Request $model
     */
    private function sendAdminNotification(Request $model)
    {
        $emailTo = trim($this->helper->getModuleConfig('admin_email/to'));
        if ($emailTo) {
            $emailTo = explode(',', $emailTo);
            $sender = $this->helper->getModuleConfig('admin_email/sender');
            $template = $this->helper->getModuleConfig('admin_email/template');
            $this->sendEmail($model, $sender, $emailTo, $template);
        }
    }

    /**
     * @param Request $model
     */
    private function sendAutoReply(Request $model)
    {
        $enabled = $this->helper->getModuleConfig('reply_email/enabled');
        if ($enabled) {
            $emailTo = [$model->getEmail()];
            $sender = $this->helper->getModuleConfig('reply_email/sender');
            $template = $this->helper->getModuleConfig('reply_email/template');
            $this->sendEmail($model, $sender, $emailTo, $template);
        }
    }

    /**
     * @param Request $model
     * @param $sender
     * @param array $emailTo
     * @param $template
     */
    private function sendEmail(Request $model, $sender, $emailTo, $template)
    {
        try {
            $store = $this->storeManager->getStore();
            $first = array_shift($emailTo);
            $data =  [
                'website_name'  => $store->getWebsite()->getName(),
                'group_name'    => $store->getGroup()->getName(),
                'store_name'    => $store->getName(),
                'store'         => $store,
                'request'       => $model,
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
                $first,
                $model->getName()
            )->addTo(
                $emailTo,
                $model->getName()
            )->getTransport();

            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
