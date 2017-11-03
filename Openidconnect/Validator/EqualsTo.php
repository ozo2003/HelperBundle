<?php

namespace Sludio\HelperBundle\Openidconnect\Validator;

class EqualsTo implements ValidInterface
{
    use ValidatorTrait;

    public function isValid($expectedValue, $actualValue)
    {
        if ($expectedValue === $actualValue) {
            return true;
        }

        $this->message = sprintf("%s is invalid as it does not equal expected %s", $actualValue, $expectedValue);

        return false;
    }
}
