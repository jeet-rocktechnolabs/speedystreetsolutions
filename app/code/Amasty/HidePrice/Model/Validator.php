<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Model;

use Laminas\Validator\EmailAddress;
use Laminas\Validator\NotEmpty;

class Validator
{
    /**
     * @var EmailAddress
     */
    private $emailAddressValidator;

    /**
     * @var NotEmpty
     */
    private $notEmptyValidator;

    public function __construct(
        EmailAddress $emailAddressValidator,
        NotEmpty $notEmptyValidator
    ) {
        $this->emailAddressValidator = $emailAddressValidator;
        $this->notEmptyValidator = $notEmptyValidator;
    }

    public function isValidEmail(string $email): bool
    {
        return $this->emailAddressValidator->isValid($email);
    }

    public function isNotEmpty(string $data): bool
    {
        return $this->notEmptyValidator->isValid($data);
    }
}
