<?php
namespace Adm\NewsletterAdminNotify\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Translate\Inline\StateInterface;

class SubscriberSaveAfter implements ObserverInterface
{
    protected $transportBuilder;
    protected $storeManager;
    protected $inlineTranslation;
    protected $logger;

    public function __construct(
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        StateInterface $inlineTranslation,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        try {
            $subscriber = $observer->getEvent()->getSubscriber();
            // Check if subscriber status is subscribed
            if ($subscriber->getSubscriberStatus() == \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED) {
                // Log for debugging
                $this->logger->info('Processing new newsletter subscription for ' . $subscriber->getSubscriberEmail());
                
                // Prepare and send the email
                $this->inlineTranslation->suspend();
                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                $transport = $this->transportBuilder
                    ->setTemplateIdentifier('12') // Email template identifier
                    ->setTemplateOptions(['area' => 'frontend', 'store' => $this->storeManager->getStore()->getId()])
                    ->setTemplateVars(['subscriber' => $subscriber])
                    ->setFrom('general') // Email sender, defined in store configuration
                    ->addTo('sales@speedystreetsolutions.com') // Replace with actual admin email
                    ->getTransport();
                
                $transport->sendMessage();
                $this->inlineTranslation->resume();
            }
        } catch (\Exception $e) {
            $this->logger->critical('Error sending newsletter subscription notification: ' . $e->getMessage());
        }
    }
}
