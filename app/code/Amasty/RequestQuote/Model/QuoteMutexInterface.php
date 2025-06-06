<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model;

use InvalidArgumentException;
use Throwable;

/**
 * Intended to prevent race conditions during quote update by concurrent requests.
 * Copy of magento class for support 23.
 * @api
 */
interface QuoteMutexInterface
{
    /**
     * Acquires a lock for quote, executes callable and releases the lock after.
     *
     * @param string[] $maskedIds
     * @param callable $callable
     * @param array $args
     * @return mixed
     * @throws InvalidArgumentException
     * @throws Throwable
     */
    public function execute(array $maskedIds, callable $callable, array $args = []);
}
