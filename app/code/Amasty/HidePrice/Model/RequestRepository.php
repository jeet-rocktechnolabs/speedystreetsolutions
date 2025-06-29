<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Model;

use Amasty\HidePrice\Api\Data;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;

class RequestRepository implements \Amasty\HidePrice\Api\RequestRepositoryInterface
{
    /**
     * @var array
     */
    protected $request = [];

    /**
     * @var ResourceModel\Request
     */
    private $requestResource;

    /**
     * @var RequestFactory
     */
    private $requestFactory;

    public function __construct(
        \Amasty\HidePrice\Model\ResourceModel\Request $requestResource,
        \Amasty\HidePrice\Model\RequestFactory $requestFactory
    ) {
        $this->requestResource = $requestResource;
        $this->requestFactory = $requestFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Data\RequestInterface $request)
    {
        if ($request->getRequestId()) {
            $request = $this->get($request->getRequestId())->addData($request->getData());
        }

        try {
            $this->requestResource->save($request);
            unset($this->request[$request->getRequestId()]);
        } catch (\Exception $e) {
            if ($request->getRequestId()) {
                throw new CouldNotSaveException(
                    __('Unable to save request with ID %1. Error: %2', [$request->getRequestId(), $e->getMessage()])
                );
            }
            throw new CouldNotSaveException(__('Unable to save new request. Error: %1', $e->getMessage()));
        }
        
        return $request;
    }

    /**
     * {@inheritdoc}
     */
    public function get($requestId)
    {
        if (!isset($this->request[$requestId])) {
            /** @var \Amasty\HidePrice\Model\Request $request */
            $request = $this->requestFactory->create();
            $this->requestResource->load($request, $requestId);
            if (!$request->getRequestId()) {
                throw new NoSuchEntityException(__('Request with specified ID "%1" not found.', $requestId));
            }
            $this->request[$requestId] = $request;
        }
        return $this->request[$requestId];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Data\RequestInterface $request)
    {
        try {
            $this->requestResource->delete($request);
            unset($this->request[$request->getRequestId()]);
        } catch (\Exception $e) {
            if ($request->getRequestId()) {
                throw new CouldNotDeleteException(
                    __('Unable to remove request with ID %1. Error: %2', [$request->getRequestId(), $e->getMessage()])
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove request rule. Error: %1', $e->getMessage()));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($requestId)
    {
        $model = $this->get($requestId);
        $this->delete($model);
        return true;
    }
}
