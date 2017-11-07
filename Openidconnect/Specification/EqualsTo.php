<?php

namespace Sludio\HelperBundle\Openidconnect\Specification;

use Sludio\HelperBundle\Script\Specification\CompositeSpecification;

class EqualsTo extends CompositeSpecification
{
    use SpecificationTrait;

    public function isSatisfiedBy($expectedValue, $actualValue)
    {
        if ($expectedValue === $actualValue) {
            return true;
        }

        $this->message = sprintf("%s is invalid as it does not equal expected %s", $actualValue, $expectedValue);

        return false;
    }
}
