<?php
namespace NYPL\Services\Model;

use NYPL\Starter\Model;
use NYPL\Starter\Model\LocalDateTime;
use NYPL\Starter\Model\ModelTrait\TranslateTrait;

/**
 * Class HoldRequestModel
 *
 * @package NYPL\Services\Model
 */
abstract class HoldRequestModel extends Model
{
    use TranslateTrait;

    /**
     * @SWG\Property(example="6779366")
     * @var string
     */
    public $patron;

    /**
     * @SWG\Property(example="sierra-nypl")
     * @var string
     */
    public $nyplSource;

    /**
     * @SWG\Property(example="hold")
     * @var string
     */
    public $requestType = 'hold';

    /**
     * @SWG\Property(example="i")
     * @var string
     */
    public $recordType;

    /**
     * @SWG\Property(example="10011630")
     * @var string
     */
    public $record;

    /**
     * @SWG\Property(example="mal")
     * @var string
     */
    public $pickupLocation;

    /**
     * @SWG\Property(example="2018-01-07T02:32:51Z", type="string")
     * @var LocalDateTime
     */
    public $neededBy;

    /**
     * @SWG\Property(example="1")
     * @var int
     */
    public $numberOfCopies;

    /**
     * @SWG\Property()
     * @var ElectronicDocumentData
     */
    public $docDeliveryData;

    /**
     * @return string
     */
    public function getPatron()
    {
        return $this->patron;
    }

    /**
     * @param string $patron
     */
    public function setPatron(string $patron)
    {
        $this->patron = $patron;
    }

    /**
     * @return string
     */
    public function getNyplSource()
    {
        return $this->nyplSource;
    }

    /**
     * @param string $nyplSource
     */
    public function setNyplSource(string $nyplSource)
    {
        $this->nyplSource = $nyplSource;
    }

    /**
     * @return string
     */
    public function getRequestType()
    {
        return $this->requestType;
    }

    /**
     * @param string $requestType
     */
    public function setRequestType(string $requestType)
    {
        $this->requestType = $requestType;
    }

    /**
     * @return string
     */
    public function getRecordType()
    {
        return $this->recordType;
    }

    /**
     * @param string $recordType
     */
    public function setRecordType(string $recordType)
    {
        $this->recordType = $recordType;
    }

    /**
     * @return string
     */
    public function getRecord()
    {
        return $this->record;
    }

    /**
     * @param string $record
     */
    public function setRecord(string $record)
    {
        $this->record = $record;
    }

    /**
     * @return string
     */
    public function getPickupLocation()
    {
        return $this->pickupLocation;
    }

    /**
     * @param string $pickupLocation
     */
    public function setPickupLocation(string $pickupLocation)
    {
        $this->pickupLocation = $pickupLocation;
    }

    /**
     * @return LocalDateTime
     */
    public function getNeededBy()
    {
        return $this->neededBy;
    }

    /**
     * @param LocalDateTime $neededBy
     */
    public function setNeededBy(LocalDateTime $neededBy)
    {
        $this->neededBy = $neededBy;
    }

    /**
     * @param string $neededBy
     *
     * @return LocalDateTime
     */
    public function translateNeededBy(string $neededBy = '')
    {
        return new LocalDateTime(LocalDateTime::FORMAT_DATE_TIME_RFC, $neededBy);
    }

    /**
     * @return string
     */
    public function getNumberOfCopies()
    {
        return $this->numberOfCopies;
    }

    /**
     * @param string $numberOfCopies
     */
    public function setNumberOfCopies(string $numberOfCopies)
    {
        $this->numberOfCopies = (int) $numberOfCopies;
    }

    /**
     * @param ElectronicDocumentData $docDeliveryData
     */
    public function setDocDeliveryData(ElectronicDocumentData $docDeliveryData)
    {
        $this->docDeliveryData = $docDeliveryData;
    }

    /**
     * @return ElectronicDocumentData
     */
    public function getDocDeliveryData()
    {
        return $this->docDeliveryData;
    }

    /**
     * @param array $data
     *
     * @return ElectronicDocumentData
     */
    public function translateDocDeliveryData(array $data)
    {
        return new ElectronicDocumentData($data, true);
    }
}
