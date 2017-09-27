<?php
namespace NYPL\Services;

use Firebase\JWT\JWT;
use NYPL\Services\Model\Response\HoldRequestErrorResponse;
use NYPL\Starter\APILogger;
use NYPL\Starter\Config;
use NYPL\Starter\Controller;
use Slim\Container;

/**
 * Class ServiceController
 *
 * @package NYPL\Services
 */
class ServiceController extends Controller
{
    const READ_REQUEST_SCOPE = 'read:hold_request';

    const WRITE_REQUEST_SCOPE = 'write:hold_request';

    const GLOBAL_REQUEST_SCOPE = 'readwrite:hold_request';

    /**
     * @var bool
     */
    public $useJobService;

    /**
     * @var Container
     */
    public $container;

    /**
     * Controller constructor.
     *
     * @param \Slim\Container $container
     * @param int $cacheSeconds
     */
    public function __construct(Container $container, int $cacheSeconds = 0)
    {
        $this->setUseJobService(Config::get('USE_JOB_SERVICE'));
        $this->setResponse($container->get('response'));
        $this->setRequest($container->get('request'));

        $this->addCacheHeader($cacheSeconds);

        $this->initializeContentType();

        $this->initializeIdentityHeader();

        parent::__construct($this->request, $this->response, $cacheSeconds);
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param Container $container
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return boolean
     */
    public function isUseJobService(): bool
    {
        return $this->useJobService;
    }

    /**
     * @param boolean $useJobService
     */
    public function setUseJobService(bool $useJobService)
    {
        $this->useJobService = $useJobService;
    }

    /**
     * @return bool
     */
    public function patronIsAuthorized()
    {
        APILogger::addDebug('Verifying patron is authorized.');

        $requestIdentity = $this->getPatronFromRequest();
        $tokenIdentity = $this->getPatronFromToken();

        return strcmp($requestIdentity, $tokenIdentity) === 0;
    }

    /**
     * @return bool
     */
    protected function hasPatronIdentifier()
    {
        $params = $this->getRequest()->getQueryParams();

        return isset($params['patron']);
    }

    /**
     * @return bool
     */
    public function isRequestAuthorized()
    {
        APILogger::addDebug('Verifying valid OAuth scope.');

        if ($this->getRequest()->getMethod() === 'GET') {
            $hasScopeAccess = $this->hasReadRequestScope();
        } else {
            $hasScopeAccess = $this->hasWriteRequestScope();
        }

        return $hasScopeAccess;
    }

    /**
     * @return bool
     */
    protected function hasReadRequestScope(): bool
    {
        return (bool) in_array(
            self::READ_REQUEST_SCOPE,
            (array) $this->getIdentityHeader()->getScopes()
        )
        || $this->hasGlobalRequestScope();
    }

    /**
     * @return bool
     */
    protected function hasWriteRequestScope(): bool
    {
        return (bool) in_array(
            self::WRITE_REQUEST_SCOPE,
            (array) $this->getIdentityHeader()->getScopes()
        )
        || $this->hasGlobalRequestScope();
    }

    /**
     * @return bool
     */
    protected function hasGlobalRequestScope(): bool
    {
        return (bool) in_array(
            self::GLOBAL_REQUEST_SCOPE,
            (array) $this->getIdentityHeader()->getScopes()
        );
    }

    /**
     * @return mixed
     */
    protected function getPatronFromRequest()
    {
        APILogger::addDebug('Retrieving patron ID from request.');
        $payload = json_decode($this->getRequest()->getBody());
        return $payload->patron;
    }

    /**
     * @return string
     */
    protected function getPublicKey()
    {
        APILogger::addDebug('Retrieving public key.');
        return file_get_contents(__DIR__ . '/../config/pubkey.pem');
    }

    /**
     * @return mixed
     */
    protected function getPatronFromToken()
    {
        APILogger::addDebug('Retrieving OAuth token.');
        $token = $this->getIdentityHeader()->getToken();
        APILogger::addDebug('Decoding OAuth token.');
        $decoded = JWT::decode($token, $this->getPublicKey(), ['RS256']);

        return $decoded->sub;
    }

    /**
     * @param \Exception $exception
     * @return \Slim\Http\Response
     */
    public function invalidScopeResponse(\Exception $exception)
    {
        return $this->getResponse()->withJson(
            new HoldRequestErrorResponse(
                '403',
                'invalid-scope',
                'Client does not have sufficient privileges. ' . $exception->getMessage()
            )
        )->withStatus(403);
    }

    /**
     * @param \Exception $exception
     * @return \Slim\Http\Response
     */
    public function invalidRequestResponse(\Exception $exception)
    {
        return $this->getResponse()->withJson(
            new HoldRequestErrorResponse(
                '400',
                'invalid-request',
                'An invalid request was sent to the API. ' . $exception->getMessage()
            )
        )->withStatus(400);
    }
}
