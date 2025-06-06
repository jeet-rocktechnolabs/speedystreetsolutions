<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Test\Integration\Controller;

use Amasty\RequestQuote\Controller\Router;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\FrontController;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Request\ValidatorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test frontend router.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 */
class RouterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FrontController
     */
    private $model;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ValidatorInterface
     */
    private $fakeRequestValidator;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(
            FrontController::class,
            ['requestValidator' => $this->createRequestValidator()]
        );

        /** @var ActionFlag $actionFlag */
        $actionFlag = $this->objectManager->get(ActionFlag::class);
        $actionFlag->set('', ActionInterface::FLAG_NO_DISPATCH, true);
    }

    /**
     * Test that standard route registration is working.
     *
     * @magentoConfigFixture default_store amasty_request_quote/general/is_active 1
     */
    public function testDispatchStandard(): void
    {
        $request = $this->getRequest('/amasty_quote/cart');

        $this->model->dispatch($request);

        $this->assertEquals(
            Router::MODULE_NAME,
            $request->getModuleName(),
            'Module is not matched by route uri key amasty_quote'
        );
    }

    /**
     * Test that standard route registration is working.
     *
     * @magentoConfigFixture default_store amasty_request_quote/general/is_active 1
     * @magentoConfigFixture default_store amasty_request_quote/general/url_key test_quote
     */
    public function testDispatchGlobal(): void
    {
        $request = $this->getRequest('/test_quote/cart');

        $this->model->dispatch($request);

        $this->assertEquals(
            Router::MODULE_NAME,
            $request->getModuleName(),
            'Custom Router is not working'
        );
    }

    /**
     * Test scoped url key value
     *
     * @magentoConfigFixture default_store amasty_request_quote/general/is_active 0
     * @magentoConfigFixture current_store amasty_request_quote/general/is_active 1
     * @magentoConfigFixture default_store amasty_request_quote/general/url_key definitely_not_q
     * @magentoConfigFixture current_store amasty_request_quote/general/url_key test_quote
     */
    public function testDispatchStoreValue(): void
    {
        $request = $this->getRequest('/test_quote/cart');

        $this->model->dispatch($request);

        $this->assertEquals(
            Router::MODULE_NAME,
            $request->getModuleName(),
            'Custom Router is not working'
        );
    }

    /**
     * @magentoConfigFixture default_store amasty_request_quote/general/is_active 1
     * @magentoConfigFixture default_store amasty_request_quote/general/url_key definitely_not_q
     */
    public function testNotDispatched(): void
    {
        $request = $this->getRequest('/test_quote/cart');

        $this->model->dispatch($request);

        $this->assertNotEquals(
            Router::MODULE_NAME,
            $request->getModuleName()
        );
    }

    /**
     * The custom router should not match if the extension is disabled.
     *
     * @magentoConfigFixture default_store amasty_request_quote/general/is_active 0
     * @magentoConfigFixture default_store amasty_request_quote/general/url_key test_quote
     */
    public function testDispatchDisabled(): void
    {
        $request = $this->getRequest('/test_quote/cart');

        $this->model->dispatch($request);

        $this->assertNotEquals(
            Router::MODULE_NAME,
            $request->getModuleName()
        );
    }

    /**
     * Case sensitivity test.
     *
     * @magentoConfigFixture default_store amasty_request_quote/general/is_active 1
     * @magentoConfigFixture default_store amasty_request_quote/general/url_key revertCASE
     */
    public function testDispatchInCase(): void
    {
        $request = $this->getRequest('/REVERTcase/cart');

        $this->model->dispatch($request);

        $this->assertEquals(
            Router::MODULE_NAME,
            $request->getModuleName()
        );
    }

    /**
     * @return ValidatorInterface
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function createRequestValidator(): ValidatorInterface
    {
        if (!$this->fakeRequestValidator) {
            $this->fakeRequestValidator = new class implements ValidatorInterface {
                public function validate(
                    RequestInterface $request,
                    ActionInterface $action
                ): void {
                }
            };
        }

        return $this->fakeRequestValidator;
    }

    private function getRequest(string $uri)
    {
        $request = $this->objectManager->get(HttpRequest::class);
        $request->setRequestUri($uri);

        return $request;
    }
}
