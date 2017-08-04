<?php

namespace Sludio\HelperBundle\Oauth\Exception;

use RuntimeException;

class MissingAuthorizationCodeException extends RuntimeException implements OAuth2ClientException
{
}
