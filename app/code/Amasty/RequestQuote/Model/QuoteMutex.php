<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model;

use InvalidArgumentException;
use Magento\Framework\App\ResourceConnection;
use Throwable;

class QuoteMutex implements QuoteMutexInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param string[] $maskedIds
     * @param callable $callable
     * @param array $args
     * @return mixed
     * @throws InvalidArgumentException
     * @throws Throwable
     */
    public function execute(array $maskedIds, callable $callable, array $args = [])
    {
        if (empty($maskedIds)) {
            throw new InvalidArgumentException('Quote masked ids must be provided');
        }

        $connection = $this->resourceConnection->getConnection();
        $connection->beginTransaction();
        $query = $connection->select()
            ->from($this->resourceConnection->getTableName('quote_id_mask'), 'entity_id')
            ->where('masked_id IN (?)', $maskedIds)
            ->forUpdate(true);
        $connection->query($query);

        try {
            $result = $callable(...$args);
            $this->resourceConnection->getConnection()->commit();
            return $result;
        } catch (Throwable $e) {
            $this->resourceConnection->getConnection()->rollBack();
            throw $e;
        }
    }
}
