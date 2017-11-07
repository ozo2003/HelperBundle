<?php

namespace Sludio\HelperBundle\Openidconnect\Specification;

class EqualsTo extends BaseSpecification
{
    public function isSatisfiedBy($expectedValue, $actualValue = null)
    {
        if ($expectedValue === $actualValue) {
            return true;
        }

        $this->message = sprintf("%s is invalid as it does not equal expected %s", $actualValue, $expectedValue);

        return false;
    }
}
