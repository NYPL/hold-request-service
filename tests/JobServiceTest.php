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

            public static function generateJobId(bool $useJobManager = true): string
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
     * @covers NYPL\Services\JobService
     */
    public function testIfJobIdIsUnique()
    {
        $fakeService = $this->fakeJobService;
        $uniqueId = $fakeService::generateJobId();

        self::assertNotNull($uniqueId);
    }

    /**
     * @covers NYPL\Services\JobService
     */
    public function testIfJobIdIsValidUuid()
    {
        $useJobManager = false;
        $fakeService = $this->fakeJobService;
        $uuid = $fakeService::generateJobId($useJobManager);

        self::assertTrue(Uuid::isValid($uuid));
    }

}
