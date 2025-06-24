<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Api;

/**
 * Interface RequestRepositoryInterface
 * @api
 */
interface RequestRepositoryInterface
{
    /**
     * @param \Amasty\HidePrice\Api\Data\RequestInterface $request
     * @return \Amasty\HidePrice\Api\Data\RequestInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Amasty\HidePrice\Api\Data\RequestInterface $request);

    /**
     * @param int $requestId
     * @return \Amasty\HidePrice\Api\Data\RequestInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($requestId);

    /**
     * @param \Amasty\HidePrice\Api\Data\RequestInterface $request
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\HidePrice\Api\Data\RequestInterface $request);

    /**
     * @param int $requestId
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($requestId);
}
