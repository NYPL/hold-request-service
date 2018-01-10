<?php
namespace NYPL\Services\Test;

use Firebase\JWT\ExpiredException;
use Slim\Http\Request;
use Slim\Http\Stream;
use NYPL\Services\Controller\HoldRequestController;
use NYPL\Services\ServiceContainer;
use NYPL\Services\Test\Mocks\MockConfig;
use NYPL\Services\Test\Mocks\MockService;
use NYPL\Starter\Model\IdentityHeader;
use PHPUnit\Framework\TestCase;

class ServiceControllerTest extends TestCase
{
    public $mockContainer;

    public function setUp()
    {
        parent::setUp();
        MockConfig::initialize(__DIR__ . '/Mocks/');
        MockService::setMockContainer();
        $this->mockContainer = MockService::getMockContainer();

        $this->fakeHoldRequestController = new class(MockService::getMockContainer(), 0) extends HoldRequestController {

            public $container;
            public $cacheSeconds;
            public $request;

            public function __construct(\Slim\Container $container, $cacheSeconds)
            {
                $this->container = new ServiceContainer();
                parent::__construct($container, $cacheSeconds);
            }

            public function setRequest(Request $request)
            {
                parent::setRequest($request);
            }

            public function initializeIdentityHeader()
            {
                $request = $this->container['request'];
                $params = [
                    'X-NYPL-Identity' =>
                        '{"token":"' . MockConfig::get('OAUTH_TOKEN') .
                        '","identity":{"sub":null,"scope":"openid offline_access api read:hold_request"}}'
                ];

                foreach ($params as $name => $value) {
                    $request = $request->withHeader($name, $value);
                }

                return $request;
            }

            public function getMockRequest()
            {
                $request = $this->initializeIdentityHeader();
                $resource = fopen(__DIR__ . '/Stubs/hold-request-post-request.json', 'r');
                $body = new Stream($resource);
                $request = $request->withMethod('POST')->withBody($body);
                $this->request = $request;

                return $this->request;
            }

        };
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->fakeHoldRequestController);
    }

    /**
     * @throws ExpiredException
     *
     * @covers NYPL\Services\ServiceController::isRequestAuthorized
     */
    public function testIfTokenIsValid()
    {
        $controller = $this->fakeHoldRequestController;
        $controller->setRequest($controller->getMockRequest());

        $controller->setIdentityHeader(new IdentityHeader($controller->request->getHeader('X-NYPL-Identity')[0]));

        self::assertFalse($controller->isRequestAuthorized());
    }

    /**
     * @covers NYPL\Services\ServiceController::invalidScopeResponse
     */
    public function testIfScopeIsValid()
    {
        $controller = $this->fakeHoldRequestController;
        $controller->setRequest($controller->getMockRequest());

        $header = json_decode($controller->request->getHeader('X-NYPL-Identity')[0]);
        $identity = $header->identity;

        $controller->setIdentityHeader(new IdentityHeader($controller->request->getHeader('X-NYPL-Identity')[0]));
        print_r($controller->isRequestAuthorized());

        self::assertContains('read:hold_request', $identity->scope);
        self::assertNotContains('write:hold_request', $identity->scope);
    }
}
