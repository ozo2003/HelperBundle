<?php

namespace Sludio\HelperBundle\Script\Security\Exception;

use Sludio\HelperBundle\Script\Security\Logger\Monolog;
use Throwable;

class ErrorException extends \Exception
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        Monolog::log($message, [], 'error', 1);
        parent::__construct($message, $code, $previous);
    }
}
