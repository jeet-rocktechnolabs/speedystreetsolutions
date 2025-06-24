<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Email;

use Amasty\Base\Model\MagentoVersion;
use Amasty\RequestQuote\Model\Di\Wrapper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\EmailMessageInterface;
use Magento\Framework\Mail\EmailMessageInterfaceFactory;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Magento\Framework\Message\MessageInterface;

class MessageBuilder
{
    /**
     * @var EmailMessageInterfaceFactory
     */
    private $emailMessageInterfaceFactory;

    /**
     * @var MimeMessageInterfaceFactory
     */
    private $mimeMessageInterfaceFactory;

    /**
     * @var EmailMessageInterface|MessageInterface
     */
    private $oldMessage;

    /**
     * @var array
     */
    private $messageParts = [];

    /**
     * @var bool
     */
    private $isNewVersion;

    public function __construct(
        MagentoVersion $magentoVersion,
        Wrapper $emailMessageInterfaceFactory,
        Wrapper $mimeMessageInterfaceFactory
    ) {
        $this->isNewVersion = version_compare($magentoVersion->get(), '2.3.3', '>=');
        $this->emailMessageInterfaceFactory = $emailMessageInterfaceFactory;
        $this->mimeMessageInterfaceFactory = $mimeMessageInterfaceFactory;
    }

    /**
     * @return EmailMessageInterface|MessageInterface
     * @throws LocalizedException
     */
    public function build()
    {
        if ($this->isNewVersion) {
            return $this->buildUsingEmailMessageInterfaceFactory();
        }

        return $this->replaceMessageBody();
    }

    /**
     * @return EmailMessageInterface
     * @throws LocalizedException
     */
    private function buildUsingEmailMessageInterfaceFactory()
    {
        $this->checkDependencies();
        $parts = $this->oldMessage->getBody()->getParts();
        $parts = array_merge($parts, $this->messageParts);
        $messageData = [
            'body' => $this->mimeMessageInterfaceFactory->create(
                ['parts' => $parts]
            ),
            'from' => $this->oldMessage->getFrom(),
            'to' => $this->oldMessage->getTo(),
            'subject' => $this->oldMessage->getSubject()
        ];
        $message = $this->emailMessageInterfaceFactory->create($messageData);

        return $message;
    }

    /**
     * @return MessageInterface
     * @throws LocalizedException
     */
    private function replaceMessageBody()
    {
        $this->checkDependencies();

        if (!empty($this->messageParts)) {
            /** @var \Laminas\Mime\Part $part */
            foreach ($this->messageParts as $part) {
                $this->oldMessage->getBody()->addPart($part);
            }

            $this->oldMessage->setBody($this->oldMessage->getBody());
        }

        return $this->oldMessage;
    }

    /**
     * @throws LocalizedException
     */
    private function checkDependencies(): void
    {
        if ($this->oldMessage === null) {
            throw new LocalizedException(__('To create a message, you need it\'s prototype...'));
        }
    }

    public function setOldMessage($oldMessage): MessageBuilder
    {
        $this->oldMessage = $oldMessage;

        return $this;
    }

    public function setMessageParts(array $messageParts): MessageBuilder
    {
        $this->messageParts = $messageParts;

        return $this;
    }
}
