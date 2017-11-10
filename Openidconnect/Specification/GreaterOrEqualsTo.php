<?php

namespace Sludio\HelperBundle\Openidconnect\Specification;

class GreaterOrEqualsTo extends AbstractSpecification
{
    public function isSatisfiedBy($expectedValue, $actualValue = null)
    {
        if ($actualValue >= $expectedValue) {
            return true;
        }

        $this->message = sprintf('%s is invalid as it is not greater than %s', $actualValue, $expectedValue);

        return false;
    }
}
