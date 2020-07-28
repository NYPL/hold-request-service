<?php
namespace NYPL\Services\Test\Controller;

use NYPL\Services\Controller\HoldRequestController;
use NYPL\Services\Model\HoldRequest\HoldRequest;
use NYPL\Services\Test\Mocks\MockConfig;
use NYPL\Services\Test\Mocks\MockService;
use NYPL\Services\Test\Stubs\MockAvroModel;
use PHPUnit\Framework\TestCase;
use Slim\Http\Request;
use Slim\Http\Response;

class HoldRequestControllerTest extends TestCase
{
    public $mockContainer;

    public function setUp()
    {
        parent::setUp();
        MockConfig::initialize(__DIR__ . '/../Mocks/');
        MockService::setMockContainer();
        $this->mockContainer = MockService::getMockContainer();

        $this->fakeHoldRequestController = new class(MockService::getMockContainer(), 0) extends HoldRequestController {

            public $container;
            public $cacheSeconds;

            public function __construct(\Slim\Container $container, $cacheSeconds)
            {
                parent::__construct($container, $cacheSeconds);
            }

            public function createHoldRequest()
            {
                $response = new Response();
                $stubResponse = preg_replace(
                    '/\s/',
                    '',
                    file_get_contents(__DIR__ . '/../Stubs/hold-request-get-response.json')
                );
                $response->getBody()->write($stubResponse);

                return $response;
            }

            public function getHoldRequests(): Response
            {
                $response = new Response();
                $stubResponse = preg_replace(
                    '/\s/',
                    '',
                    file_get_contents(__DIR__ . '/../Stubs/hold-requests-get-response.json')
                );
                $response->getBody()->write($stubResponse);

                return $response;
            }

            public function getHoldRequest(Request $request, Response $response, array $args)
            {
                $response = new Response();
                $stubResponse = preg_replace(
                    '/\s/',
                    '',
                    file_get_contents(__DIR__ . '/../Stubs/hold-request-get-response.json')
                );
                $response->getBody()->write($stubResponse);

                return $response;
            }

            public function updateHoldRequest(Request $request, Response $response, array $args)
            {
                $response = new Response();
                $stubResponse = preg_replace(
                    '/\s/',
                    '',
                    file_get_contents(__DIR__ . '/../Stubs/hold-request-get-response.json')
                );
                $response->getBody()->write($stubResponse);

                return $response;
            }
        };
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->fakeHoldRequestController);
    }

    /**
     * @covers NYPL\Services\Controller\HoldRequestController::createHoldRequest
     */
    public function testCreationOfHoldRequest()
    {
        $controller = $this->fakeHoldRequestController;

        $response = $controller->createHoldRequest();

        $requestData = '{
            "data": {
                    "id": 43,
                    "jobId": "113159483425338de",
                    "createdDate": "2017-06-19T16:29:25-04:00",
                    "updatedDate": null,
                    "success": false,
                    "processed": false,
                    "deliveryLocation": null,
                    "patron": "67793666",
                    "nyplSource": "sierra-nypl",
                    "requestType": "hold",
                    "recordType": "i",
                    "record": "32312222x",
                    "pickupLocation": "sasb",
                    "neededBy": "2016-01-07T02:32:51+00:00",
                    "numberOfCopies": 1,
                    "docDeliveryData": {
                        "emailAddress": null,
                        "chapterTitle": null,
                        "startPage": null,
                        "endPage": null,
                        "author": null,
                        "issue": null,
                        "volume": null,
                        "requestNotes": null,
                        "date": null
                    }
                }
            }';
        $createResponse = preg_replace('/\s/', '', $requestData);

        $body = $response->getBody();
        self::assertTrue($response->getStatusCode() == 200);
        self::assertSame(
            $createResponse,
            $body->__toString()
        );
    }

    /**
     * @covers NYPL\Services\Controller\HoldRequestController::getHoldRequests
     */
    public function testBulkGetHoldRequests()
    {
        $controller = $this->fakeHoldRequestController;

        $response = $controller->getHoldRequests();

        $bulkData = '{
            "data": [
                {
                    "id": 34,
                    "jobId": "113159483425338de",
                    "createdDate": "2017-06-19T16:29:25-04:00",
                    "updatedDate": null,
                    "success": false,
                    "processed": false,
                    "deliveryLocation": null,
                    "patron": "67793666",
                    "nyplSource": "sierra-nypl",
                    "requestType": "hold",
                    "recordType": "i",
                    "record": "32312222x",
                    "pickupLocation": "sasb",
                    "neededBy": "2016-01-07T02:32:51+00:00",
                    "numberOfCopies": 1,
                    "docDeliveryData": {
                        "emailAddress": null,
                        "chapterTitle": null,
                        "startPage": null,
                        "endPage": null,
                        "author": null,
                        "issue": null,
                        "volume": null,
                        "requestNotes": null,
                        "date": null
                    }
                }
                ]
            }';
        $bulkResponse = preg_replace('/\s/', '', $bulkData);

        $body = $response->getBody();
        self::assertTrue($response->getStatusCode() == 200);
        self::assertSame(
            $bulkResponse,
            $body->__toString()
        );
    }

    /**
     * @covers NYPL\Services\Controller\HoldRequestController::getHoldRequest
     */
    public function testSingleGetHoldRequest()
    {
        $serverParams = [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/api/v0.1/hold-requests/43',
            'CONTENT_TYPE' => 'application/json;charset=utf8',
        ];

        $data = '{"id": 43,"jobId": "113159483425338de","createdDate": "2017-06-19T16:29:25-04:00","updatedDate": null,"success": false,"processed": false,"deliveryLocation": null,"patron": "300135","nyplSource": "sierra-nypl","requestType": "hold","recordType": "i","record": "32312222x","pickupLocation": "sasb","neededBy": "2016-01-07T02:32:51Z","numberOfCopies": "1","docDeliveryData": null}';
        $avro = new MockAvroModel(new HoldRequest(), $data);
        $modelJson = json_encode($avro->modelAsArray());

        $controller = $this->fakeHoldRequestController;

        $response = $controller->getHoldRequest(
            $this->mockContainer['request'],
            $this->mockContainer['response'],
            []
        );

        $singleData = '{
            "data": {
                    "id": 43,
                    "jobId": "113159483425338de",
                    "createdDate": "2017-06-19T16:29:25-04:00",
                    "updatedDate": null,
                    "success": false,
                    "processed": false,
                    "deliveryLocation": null,
                    "patron": "67793666",
                    "nyplSource": "sierra-nypl",
                    "requestType": "hold",
                    "recordType": "i",
                    "record": "32312222x",
                    "pickupLocation": "sasb",
                    "neededBy": "2016-01-07T02:32:51+00:00",
                    "numberOfCopies": 1,
                    "docDeliveryData": {
                        "emailAddress": null,
                        "chapterTitle": null,
                        "startPage": null,
                        "endPage": null,
                        "author": null,
                        "issue": null,
                        "volume": null,
                        "requestNotes": null,
                        "date": null
                    }
                }
            }';
        $singleResponse = preg_replace('/\s/', '', $singleData);

        $body = $response->getBody();
        self::assertTrue($response->getStatusCode() == 200);
        self::assertSame(
            $singleResponse,
            $body->__toString()
        );
    }


    /**
     * @covers NYPL\Services\Controller\HoldRequestController::updateHoldRequest
     */
    public function testUpdatingAHoldRequest()
    {
        $controller = $this->fakeHoldRequestController;

        $response = $controller->updateHoldRequest(
            $this->mockContainer['request'],
            $this->mockContainer['response'],
            []
        );

        $requestData = '{
            "data": {
                    "id": 43,
                    "jobId": "113159483425338de",
                    "createdDate": "2017-06-19T16:29:25-04:00",
                    "updatedDate": null,
                    "success": false,
                    "processed": false,
                    "deliveryLocation": null,
                    "patron": "67793666",
                    "nyplSource": "sierra-nypl",
                    "requestType": "hold",
                    "recordType": "i",
                    "record": "32312222x",
                    "pickupLocation": "sasb",
                    "neededBy": "2016-01-07T02:32:51+00:00",
                    "numberOfCopies": 1,
                    "docDeliveryData": {
                        "emailAddress": null,
                        "chapterTitle": null,
                        "startPage": null,
                        "endPage": null,
                        "author": null,
                        "issue": null,
                        "volume": null,
                        "requestNotes": null,
                        "date": null
                    }
                }
            }';
        $updateResponse = preg_replace('/\s/', '', $requestData);

        $body = $response->getBody();
        self::assertTrue($response->getStatusCode() == 200);
        self::assertSame(
            $updateResponse,
            $body->__toString()
        );
    }
}
