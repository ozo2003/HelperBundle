<?php

namespace Sludio\HelperBundle\Script\Specification;

interface SpecificationInterface
{

    /**
     * @param $expectedValue
     * @param $actualValue
     *
     * @return boolean
     */
    public function isSatisfiedBy($expectedValue, $actualValue = null);

    /**
     * And
     *
     * @param SpecificationInterface $specification
     *
     * @return SpecificationInterface
     */
    public function andX(SpecificationInterface $specification);

    /**
     * Or
     *
     * @param SpecificationInterface $specification
     *
     * @return SpecificationInterface
     */
    public function orX(SpecificationInterface $specification);

    /**
     * Not
     * @return SpecificationInterface
     * @internal param SpecificationInterface $specification
     *
     */
    public function not();
}
