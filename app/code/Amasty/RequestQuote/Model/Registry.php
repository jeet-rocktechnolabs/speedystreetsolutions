<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model;

class Registry
{
    /**
     * @var array
     */
    private $registry = [];

    /**
     * @param string $key
     * @return mixed|null
     */
    public function registry(string $key)
    {
        if (isset($this->registry[$key])) {
            return $this->registry[$key];
        }
        return null;
    }

    /**
     * @param string $key
     * @param $value
     * @param bool $graceful
     */
    public function register(string $key, $value, bool $graceful = false): void
    {
        if (isset($this->registry[$key])) {
            if ($graceful) {
                return;
            }
            throw new \RuntimeException('Registry key "' . $key . '" already exists');
        }
        $this->registry[$key] = $value;
    }

    public function unregister(string $key): void
    {
        if (isset($this->registry[$key])) {
            if (is_object($this->registry[$key])
                && method_exists($this->registry[$key], '__destruct')
                && is_callable([$this->registry[$key], '__destruct'])
            ) {
                $this->registry[$key]->__destruct();
            }
            unset($this->registry[$key]);
        }
    }

    /**
     * Destruct registry items
     */
    public function __destruct()
    {
        $keys = array_keys($this->registry);
        array_walk($keys, [$this, 'unregister']);
    }
}
