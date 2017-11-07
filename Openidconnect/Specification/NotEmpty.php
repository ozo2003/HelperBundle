<?php

namespace Sludio\HelperBundle\Openidconnect\Specification;

class NotEmpty extends BaseSpecification
{
    public function isSatisfiedBy($expectedValue, $actualValue = null)
    {
        $valid = !empty($actualValue);
        if (!$valid) {
            $this->message = sprintf("%s is required and cannot be empty", $this->getName());
        }

        return $valid;
    }
}
