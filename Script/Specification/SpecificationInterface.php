<?php

namespace Sludio\HelperBundle\Script\Specification;

interface SpecificationInterface
{
    /**
     * Process specification satisfaction
     *
     * @param object $object
     *
     * @return boolean
     */
    public function isSatisfiedBy($object);

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
