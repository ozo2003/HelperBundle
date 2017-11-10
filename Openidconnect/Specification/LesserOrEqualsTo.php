<?php

namespace Sludio\HelperBundle\Openidconnect\Specification;

class LesserOrEqualsTo extends AbstractSpecification
{
    public function isSatisfiedBy($expectedValue, $actualValue = null)
    {
        if ($actualValue <= $expectedValue) {
            return true;
        }

        $this->message = sprintf('%s is invalid as it is not less than %s', $actualValue, $expectedValue);

        return false;

    }
}
