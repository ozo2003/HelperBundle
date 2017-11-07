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
    public function isSatisfiedBy($expectedValue, $actualValue);

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
     *
     * @param SpecificationInterface $specification
     *
     * @return SpecificationInterface
     */
    public function not();
}
