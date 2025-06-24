<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magmodules\GoogleShopping\Api\Log;

/**
 * Log repository interface
 * @api
 */
interface RepositoryInterface
{

    /**
     * Limit stream size to 100 lines
     */
    public const STREAM_DEFAULT_LIMIT = 100;

    /**
     * Log file path pattern
     */
    public const LOG_FILE = '%s/log/googleshopping-%s.log';

    /**
     * Add record to error log
     *
     * @param string $type
     * @param mixed $data
     */
    public function addErrorLog(string $type, $data);

    /**
     * Add record to debug log
     *
     * @param string $type
     * @param mixed $data
     */
    public function addDebugLog(string $type, $data);

    /**
     * Returns path of logfile
     *
     * @param string $type
     * @return string
     */
    public function getLogFilePath(string $type): ?string;

    /**
     * Return log entries as sorted array
     *
     * @param string $path
     * @param int|null $limit
     * @return array|null
     */
    public function getLogEntriesAsArray(string $path, ?int $limit = null): ?array;
}
