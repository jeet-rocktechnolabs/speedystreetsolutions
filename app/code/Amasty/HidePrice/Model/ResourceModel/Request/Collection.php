<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Model\ResourceModel\Request;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Amasty\HidePrice\Model\Request::class, \Amasty\HidePrice\Model\ResourceModel\Request::class);
    }

    /**
     * @param array $ids
     */
    public function deleteByIds(array $ids)
    {
        $this->getConnection()->delete(
            $this->getMainTable(),
            ['request_id IN(?)' => $ids]
        );
    }
}
