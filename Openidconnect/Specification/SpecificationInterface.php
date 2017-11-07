<?php

namespace Sludio\HelperBundle\Openidconnect\Specification;

use Sludio\HelperBundle\Script\Specification\SpecificationInterface as BaseSpecification;

interface SpecificationInterface extends BaseSpecification
{
    public function getName();

    public function getMessage();
}
