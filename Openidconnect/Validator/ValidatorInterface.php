<?php

namespace Sludio\HelperBundle\Openidconnect\Validator;

interface ValidatorInterface
{
    public function getName();
    public function isValid($expectedValue, $actualValue);
    public function getMessage();
}
