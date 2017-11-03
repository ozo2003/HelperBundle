<?php

namespace Sludio\HelperBundle\Openidconnect\Validator;

class NotEmpty implements ValidInterface
{
    use ValidatorTrait;

    public function isValid($expectedValue, $actualValue)
    {
        $valid = !empty($actualValue);
        if (!$valid) {
            $this->message = sprintf("%s is required and cannot be empty", $this->getName());
        }

        return $valid;
    }
}
