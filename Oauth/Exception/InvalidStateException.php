<?php

namespace Sludio\HelperBundle\Oauth\Exception;

use RuntimeException;

class InvalidStateException extends RuntimeException implements OAuth2ClientException
{
}
