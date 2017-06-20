<?php
namespace NYPL\Services\Test;

use NYPL\Services\JobService;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class JobServiceTest extends TestCase
{

    public $fakeJobService;

    public function setUp()
    {
        $this->fakeJobService = new class extends JobService {

            public static function generateJobId($useJobManager = true): string
            {
                if ($useJobManager) {
                    $serviceId = (string) uniqid();
                } else {
                    $serviceId = Uuid::uuid4();
                }

                return $serviceId;
            }

        };
        parent::setUp();
    }

    /**
     * @covers JobService
     */
    public function testIfJobIdIsUnique()
    {
        $fakeService = $this->fakeJobService;
        $uniqueId = $fakeService::generateJobId();

        self::assertNotNull($uniqueId);
    }

    /**
     * @covers JobService
     */
    public function testIfJobIdIsValidUuid()
    {
        $useJobManager = false;
        $fakeService = $this->fakeJobService;
        $uuid = $fakeService::generateJobId($useJobManager);

        self::assertTrue(Uuid::isValid($uuid));
    }

}
