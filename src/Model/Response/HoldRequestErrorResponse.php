<?php
namespace NYPL\Services\Model\Response;

use NYPL\Starter\Model\Response\ErrorResponse;

/**
 * @SWG\Definition(title="HoldRequestErrorResponse", type="object")
 */
class HoldRequestErrorResponse extends ErrorResponse
{
    public function __construct($statusCode = 500, $type = '', $message = '', $exception = null)
    {
        parent::__construct($statusCode, $type, $message, $exception);
    }
}
