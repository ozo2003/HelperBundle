<?php

namespace Sludio\HelperBundle\Openidconnect\Specification;

class NotEmpty extends AbstractSpecification
{
    public function isSatisfiedBy($expectedValue, $actualValue = null)
    {
        if (!empty($actualValue)) {
            return true;
        }

        $this->message = sprintf('%s is required and cannot be empty', $this->getName());

        return false;
    }
}
