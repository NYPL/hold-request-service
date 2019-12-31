<?php
namespace NYPL\Services;
use NYPL\Starter\APIClient;

class PatronEligibilityClient extends APIClient
{
    protected function isRequiresAuth()
    {
        return true;
    }
}
