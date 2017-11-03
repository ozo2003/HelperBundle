<?php

namespace Sludio\HelperBundle\Openidconnect\Validator;

interface ValidInterface
{
    public function getName();

    public function isValid($expectedValue, $actualValue);

    public function getMessage();
}
