<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Model\Source;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Option\ArrayInterface;
use Magento\Framework\DB\Select;

class CustomForm implements ArrayInterface
{
    public const CUSTOMFORM_TABLE = 'amasty_customform_form';

    /**
     * @var ResourceConnection
     */
    private $resource;

    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName(self::CUSTOMFORM_TABLE);
        if ($connection->isTableExists($tableName)) {
            $select = $connection->select()
                ->from($tableName)
                ->reset(Select::COLUMNS)
                ->columns(['value' => 'form_id', 'label' => 'title']);

            $options = $connection->fetchAll($select);
        }

        return $options;
    }
}
