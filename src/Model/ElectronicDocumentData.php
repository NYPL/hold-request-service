<?php
namespace NYPL\Services\Model;

use NYPL\Starter\Model;
use NYPL\Starter\Model\ModelTrait\TranslateTrait;

/**
 * @SWG\Definition(title="ElectronicDocumentRequest", type="object")
 *
 * @package NYPL\Services\Model
 */
class ElectronicDocumentData extends Model
{
    use TranslateTrait;

    /**
     * @SWG\Property(example="user@example.com")
     * @var string
     */
    public $emailAddress;

    /**
     * @SWG\Property(example="Chapter One")
     * @var string
     */
    public $chapterTitle;

    /**
     * @SWG\Property(example="100")
     * @var string
     */
    public $startPage;

    /**
     * @SWG\Property(example="150")
     * @var string
     */
    public $endPage;

    /**
     * @SWG\Property(example="Anonymous")
     * @var string
     */
    public $author;

    /**
     * @SWG\Property(example="Summer 2017")
     * @var string
     */
    public $issue;

    /**
     * @SWG\Property(example="159")
     * @var string
     */
    public $volume;

    /**
     * @SWG\Property(example="Backup physical delivery requested.")
     * @var string
     */
    public $requestNotes;

    /**
     * @SWG\Property(example="1974")
     * @var string
     */
    public $date;

    /**
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * @param string $emailAddress
     */
    public function setEmailAddress(string $emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    /**
     * @return string
     */
    public function getChapterTitle()
    {
        return $this->chapterTitle;
    }

    /**
     * @param string $chapterTitle
     */
    public function setChapterTitle(string $chapterTitle)
    {
        $this->chapterTitle = $chapterTitle;
    }

    /**
     * @return null|string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param null|string $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getStartPage()
    {
        return $this->startPage;
    }

    /**
     * @param string $startPage
     */
    public function setStartPage(string $startPage)
    {
        $this->startPage = $startPage;
    }

    /**
     * @return string
     */
    public function getEndPage()
    {
        return $this->endPage;
    }

    /**
     * @param string $endPage
     */
    public function setEndPage(string $endPage)
    {
        $this->endPage = $endPage;
    }

    /**
     * @return null|string
     */
    public function getIssue()
    {
        return $this->issue;
    }

    /**
     * @param null|string $issue
     */
    public function setIssue($issue)
    {
        $this->issue = $issue;
    }

    /**
     * @return null|string
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * @param null|string $volume
     */
    public function setVolume($volume)
    {
        $this->volume = $volume;
    }

    /**
     * @return null|string
     */
    public function getRequestNotes()
    {
        return $this->requestNotes;
    }

    /**
     * @param null|string $requestNotes
     */
    public function setRequestNotes($requestNotes)
    {
        $this->requestNotes = $requestNotes;
    }

    /**
     * @return null|string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param null|string $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }
}
