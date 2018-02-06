<?php
namespace NYPL\Services\Controller;

use NYPL\Services\Filter\DateQueryFilter;
use NYPL\Services\JobService;
use NYPL\Services\ServiceController;
use NYPL\Services\Model\HoldRequest\HoldRequest;
use NYPL\Services\Model\Response\HoldRequestResponse;
use NYPL\Services\Model\Response\HoldRequestErrorResponse;
use NYPL\Services\Model\Response\HoldRequestsResponse;
use NYPL\Starter\APIException;
use NYPL\Starter\APILogger;
use NYPL\Starter\Filter;
use NYPL\Starter\ModelSet;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class HoldRequestController
 *
 * @package NYPL\Services\Controller
 */
class HoldRequestController extends ServiceController
{
    /**
     * @SWG\Post(
     *     path="/v0.1/hold-requests",
     *     summary="Create new hold request",
     *     tags={"hold-requests"},
     *     operationId="createHoldRequest",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="NewHoldRequest",
     *         in="body",
     *         description="Request object based on the included data model",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/NewHoldRequest")
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful operation",
     *         @SWG\Schema(ref="#/definitions/HoldRequestResponse")
     *     ),
     *     @SWG\Response(
     *         response="401",
     *         description="Unauthorized"
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Not found",
     *         @SWG\Schema(ref="#/definitions/HoldRequestErrorResponse")
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="Generic server error",
     *         @SWG\Schema(ref="#/definitions/HoldRequestErrorResponse")
     *     ),
     *     security={
     *         {
     *             "api_auth": {"openid offline_access api write:hold_request readwrite:hold_request"}
     *         }
     *     }
     * )
     *
     * @throws APIException
     * @return Response
     */
    public function createHoldRequest()
    {
        try {
            $data = $this->getRequest()->getParsedBody();

            $data['jobId'] = JobService::generateJobId($this->isUseJobService());
            $data['success'] = $data['processed'] = false;

            $holdRequest = new HoldRequest($data);

            APILogger::addDebug('POST request sent.', $data);

            try {
                $holdRequest->validatePostData();
            } catch (APIException $exception) {
                return $this->invalidRequestResponse($exception);
            }

            $holdRequest->create();

            if ($this->isUseJobService()) {
                APILogger::addDebug('Initiating job via Job Service API.', ['jobID' => $holdRequest->getJobId()]);
                JobService::beginJob($holdRequest);
            }

            return $this->getResponse()->withJson(
                new HoldRequestResponse($holdRequest)
            );
        } catch (\AvroIOTypeException $exception) {
            APILogger::addDebug('AvroIOTypeException thrown.', [$exception->getMessage()]);
            throw new APIException(
                'Request could not be validated according to the Avro data model.',
                $exception->getMessage(),
                0,
                $exception,
                400
            );
        } catch (\Exception $exception) {
            $errorType = 'create-hold-request-error';
            $errorMsg = 'Unable to create hold request due to a problem with dependent services.';

            return $this->processException($errorType, $errorMsg, $exception, $this->getRequest());
        }
    }

    /**
     * @SWG\Get(
     *     path="/v0.1/hold-requests",
     *     summary="Get a list of hold requests",
     *     tags={"hold-requests"},
     *     operationId="getHoldRequests",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="patron",
     *         in="query",
     *         required=false,
     *         type="string",
     *         description="ID of patron provided by ILS"
     *     ),
     *     @SWG\Parameter(
     *         name="record",
     *         in="query",
     *         required=false,
     *         type="string",
     *         description="ID of record provided by ILS"
     *     ),
     *     @SWG\Parameter(
     *         name="success",
     *         in="query",
     *         required=false,
     *         type="string",
     *         description="Success status of a hold request."
     *     ),
     *     @SWG\Parameter(
     *         name="processed",
     *         in="query",
     *         required=false,
     *         type="string",
     *         description="Process status of a hold request."
     *     ),
     *     @SWG\Parameter(
     *         name="createdDate",
     *         in="query",
     *         required=false,
     *         type="string",
     *         description="Creation date of a hold request. (Format: YYYY-MM-DD)"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful operation",
     *         @SWG\Schema(ref="#/definitions/HoldRequestsResponse")
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Not found",
     *         @SWG\Schema(ref="#/definitions/HoldRequestErrorResponse")
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="Generic server error",
     *         @SWG\Schema(ref="#/definitions/HoldRequestErrorResponse")
     *     ),
     *     security={
     *         {
     *             "api_auth": {"openid offline_access api read:hold_request readwrite:hold_request"}
     *         }
     *     }
     * )
     *
     * @return Response
     */
    public function getHoldRequests()
    {
        try {
            $dateFilter = null;
            if ($this->getRequest()->getParam('createdDate')) {
                $dateFilter = new DateQueryFilter(
                    'createdDate',
                    $this->getRequest()->getParam('createdDate'),
                    false,
                    '',
                    'LIKE'
                );
            }

            return  $this->getDefaultReadResponse(
                new ModelSet(new HoldRequest()),
                new HoldRequestsResponse(),
                $dateFilter,
                ['patron', 'record', 'success', 'processed']
            );
        } catch (\Exception $exception) {
            $errorType = 'get-bulk-hold-requests-error';
            $errorMsg = 'Unable to retrieve bulk hold requests. ' . $exception->getMessage();

            return $this->processException($errorType, $errorMsg, $exception, $this->getRequest());
        }
    }

    /**
     * @SWG\Get(
     *     path="/v0.1/hold-requests/{id}",
     *     summary="Get a single hold request",
     *     tags={"hold-requests"},
     *     operationId="getHoldRequest",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         type="string",
     *         format="string",
     *         description="ID of hold request"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful operation",
     *         @SWG\Schema(ref="#/definitions/HoldRequestResponse")
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Not found",
     *         @SWG\Schema(ref="#/definitions/HoldRequestErrorResponse")
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="Generic server error",
     *         @SWG\Schema(ref="#/definitions/HoldRequestErrorResponse")
     *     ),
     *     security={
     *         {
     *             "api_auth": {"openid offline_access api read:hold_request readwrite:hold_request"}
     *         }
     *     }
     * )
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     * @throws APIException
     */
    public function getHoldRequest(Request $request, Response $response, array $args)
    {
        try {
            return  $this->getDefaultReadResponse(
                new HoldRequest(),
                new HoldRequestResponse(),
                new Filter(null, null, false, $args['id'])
            );
        } catch (\Exception $exception) {
            $errorType = 'get-hold-request-error';
            $errorMsg = 'Unable to retrieve hold request. ' . $exception->getMessage();

            return $this->processException($errorType, $errorMsg, $exception, $request);
        }
    }

    /**
     * @SWG\Patch(
     *     path="/v0.1/hold-requests/{id}",
     *     summary="Update a hold request",
     *     tags={"hold-requests"},
     *     operationId="updateHoldRequest",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         description="ID of hold request",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="string",
     *         format="string"
     *     ),
     *     @SWG\Parameter(
     *         name="HoldRequest",
     *         in="body",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/HoldRequest")
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful operation",
     *         @SWG\Schema(ref="#/definitions/HoldRequestResponse")
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Not found",
     *         @SWG\Schema(ref="#/definitions/HoldRequestErrorResponse")
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="Generic server error",
     *         @SWG\Schema(ref="#/definitions/HoldRequestErrorResponse")
     *     ),
     *     security={
     *         {
     *             "api_auth": {"openid offline_access api write:hold_request readwrite:hold_request"}
     *         }
     *     }
     * )
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function updateHoldRequest(Request $request, Response $response, array $args)
    {
        try {
            if (!$this->isRequestAuthorized()) {
                APILogger::addInfo('Invalid scope received. Client not authorized to update hold requests.');
                return $this->invalidScopeResponse(new APIException(
                    'Client not authorized to update hold requests.',
                    null,
                    0,
                    null,
                    403
                ));
            }

            $data = $this->getRequest()->getParsedBody();

            $holdRequest = new HoldRequest();

            APILogger::addDebug('Raw PATCH request sent.', [(string)$request->getUri(), $request->getParsedBody()]);
            APILogger::addDebug('PATCH request sent.', [(string)$request->getUri(), $data]);

            try {
                $holdRequest->validatePatchData((array)$data);
            } catch (APIException $exception) {
                return $this->invalidRequestResponse($exception);
            }

            $holdRequest->addFilter(new Filter('id', $args['id']));
            $holdRequest->read();
            $holdRequest->update(
                $this->getRequest()->getParsedBody()
            );

            APILogger::addDebug('Database record updated.');

            if ($this->isUseJobService()) {
                APILogger::addDebug('Updating an existing job.', ['jobID' => $holdRequest->getJobId()]);
                JobService::finishJob($holdRequest);
            }

            APILogger::addDebug(
                'PATCH response',
                (array)$this->getResponse()->withJson(new HoldRequestResponse($holdRequest))
            );

            return $this->getResponse()->withJson(new HoldRequestResponse($holdRequest));
        } catch (\Exception $exception) {
            APILogger::addDebug('Exception thrown.', [$exception->getMessage()]);
            $errorType = 'update-hold-request-error';
            $errorMsg = 'Unable to update hold request.';

            return $this->processException($errorType, $errorMsg, $exception, $request);
        }
    }

    /**
     * @param string     $errorType
     * @param string     $errorMessage
     * @param \Exception $exception
     * @param Request    $request
     * @return \Slim\Http\Response
     */
    protected function processException($errorType, $errorMessage, \Exception $exception, Request $request)
    {
        $statusCode = 500;
        if ($exception instanceof APIException) {
            $statusCode = $exception->getHttpCode();
        }

        APILogger::addLog(
            $statusCode,
            get_class($exception) . ': ' . $exception->getMessage(),
            [
                $request->getHeaderLine('X-NYPL-Log-Stream-Name'),
                $request->getHeaderLine('X-NYPL-Request-ID'),
                (string) $request->getUri(),
                $request->getParsedBody()
            ]
        );

        if ($exception instanceof APIException) {
            if ($exception->getPrevious()) {
                $exception->setDebugInfo($exception->getPrevious()->getMessage());
            }
            APILogger::addDebug('APIException debug info.', [$exception->debugInfo]);
        }

        $errorResp = new HoldRequestErrorResponse(
            $statusCode,
            $errorType,
            $errorMessage,
            $exception
        );

        return $this->getResponse()->withJson($errorResp)->withStatus($statusCode);
    }
}
