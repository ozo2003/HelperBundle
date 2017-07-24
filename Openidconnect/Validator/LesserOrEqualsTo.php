<?php

namespace Sludio\HelperBundle\Openidconnect\Validator;

class LesserOrEqualsTo implements ValidatorInterface
{
    use ValidatorTrait;

    public function isValid($expectedValue, $actualValue)
    {
        if ($actualValue <= $expectedValue) {
            return true;
        }

        $this->message = sprintf("%s is invalid as it is not less than %s", $actualValue, $expectedValue);
        return false;

    }
}
