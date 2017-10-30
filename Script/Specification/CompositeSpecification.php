<?php

namespace Sludio\HelperBundle\Script\Specification;

/**
 * Composite specification
 */
abstract class CompositeSpecification implements SpecificationInterface
{
    /**
     * {@inheritdoc}
     */
    public function andX(SpecificationInterface $specification)
    {
        return new AndSpecification($this, $specification);
    }

    /**
     * {@inheritdoc}
     */
    public function orX(SpecificationInterface $specification)
    {
        return new OrSpecification($this, $specification);
    }

    /**
     * {@inheritdoc}
     */
    public function not()
    {
        return new NotSpecification($this);
    }
}