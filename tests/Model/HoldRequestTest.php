<?php
namespace NYPL\Services\Test;

use NYPL\Services\Model\HoldRequest\HoldRequest;
use PHPUnit\Framework\TestCase;

class HoldRequestTest extends TestCase
{
    public $fakeHoldRequest;

    public function setUp()
    {
        $this->fakeHoldRequest = new class extends HoldRequest {
            public function __construct($data = ['requestType' => 'retrieval'])
            {
                parent::__construct($data);
            }

            /**
             * @param null|string $requestType
             */
            public function setRequestType($requestType)
            {
                if ($requestType == 'hold' || $requestType == 'edd') {
                    $this->requestType = $requestType;
                } else {
                    $this->requestType = 'hold';
                }
            }
        };
        parent::setUp();
    }

    /**
     * @covers NYPL\Services\Model\HoldRequest\NewHoldRequest
     */
    public function testAlwaysReturnsValidRequestType()
    {
        $this->assertEquals('hold', $this->fakeHoldRequest->requestType);
        $this->fakeHoldRequest->setRequestType('edd');
        $this->assertEquals('edd', $this->fakeHoldRequest->requestType);
    }
}
