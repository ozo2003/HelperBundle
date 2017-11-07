<?php

namespace Sludio\HelperBundle\Openidconnect\Specification;

use Sludio\HelperBundle\Script\Specification\CompositeSpecification;

class GreaterOrEqualsTo extends CompositeSpecification
{
    use SpecificationTrait;

    public function isSatisfiedBy($expectedValue, $actualValue)
    {
        if ($actualValue >= $expectedValue) {
            return true;
        }

        $this->message = sprintf("%s is invalid as it is not greater than %s", $actualValue, $expectedValue);

        return false;
    }
}
