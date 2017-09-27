<?php
namespace NYPL\Services\Model\HoldRequest;

use NYPL\Services\Model\HoldRequestModel;
use NYPL\Starter\APILogger;
use NYPL\Starter\Model\ModelTrait\TranslateTrait;

/**
 * @SWG\Definition(title="NewHoldRequest", type="object")
 *
 * @package NYPL\Services\Model\HoldRequest
 */
class NewHoldRequest extends HoldRequestModel
{
    use TranslateTrait;

    const VALID_REQUEST_TYPES = ['hold', 'edd'];

    /**
     * @param null|string $requestType
     * @return bool
     */
    protected function isValidRequestType($requestType)
    {
        return in_array($requestType, self::VALID_REQUEST_TYPES);
    }

    /**
     * @param null|string $requestType
     */
    public function setRequestType($requestType)
    {
        if ($this->isValidRequestType($requestType)) {
            $this->requestType = $requestType;
        } else {
            $this->requestType = 'hold';
            APILogger::addDebug('Invalid requestType received. Reset to default "hold."');
        }
    }
}
