<?php

namespace Sludio\HelperBundle\Openidconnect\Specification;

use Sludio\HelperBundle\Script\Specification\CompositeSpecification;

class NotEmpty extends CompositeSpecification
{
    use SpecificationTrait;

    public function isSatisfiedBy($expectedValue, $actualValue)
    {
        $valid = !empty($actualValue);
        if (!$valid) {
            $this->message = sprintf("%s is required and cannot be empty", $this->getName());
        }

        return $valid;
    }
}
