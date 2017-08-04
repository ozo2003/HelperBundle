<?php

namespace Sludio\HelperBundle\Openidconnect\Validator;

interface Valid
{
    public function getName();

    public function isValid($expectedValue, $actualValue);

    public function getMessage();
}
