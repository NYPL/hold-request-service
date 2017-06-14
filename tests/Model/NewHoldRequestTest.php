<?php

use NYPL\Services\Model\HoldRequest\NewHoldRequest;

class NewHoldRequestTest extends PHPUnit_Framework_TestCase
{
    public $fakeHoldRequest;

    public function setUp()
    {
        $this->fakeHoldRequest = new class extends NewHoldRequest {
            public function __construct($data = ['requestType' => 'retrieval'])
            {
                parent::__construct($data);
            }
        };
        parent::setUp();
    }

    public function testAlwaysReturnsValidRequestType()
    {
        $this->assertEquals('hold', $this->fakeHoldRequest->requestType);
        $this->fakeHoldRequest->setRequestType('edd');
        $this->assertEquals('edd', $this->fakeHoldRequest->requestType);
    }
}
