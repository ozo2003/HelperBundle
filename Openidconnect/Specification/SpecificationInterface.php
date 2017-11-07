<?php

namespace Sludio\HelperBundle\Openidconnect\Specification;

use Sludio\HelperBundle\Script\Specification\SpecificationInterface as BaseSpecificationInterface;

interface SpecificationInterface extends BaseSpecificationInterface
{
    public function getName();

    public function getMessage();
}
