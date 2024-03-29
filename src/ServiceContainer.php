<?php
namespace NYPL\Services;

use NYPL\Starter\Config;
use NYPL\Starter\DefaultContainer;

/**
 * Class ServiceContainer
 *
 * @package NYPL\Services
 */
class ServiceContainer extends DefaultContainer
{
    /**
     * ServiceContainer constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this["settings"]["displayErrorDetails"] = Config::get('DISPLAY_ERRORS');
    }
}
