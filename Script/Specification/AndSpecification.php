<?php

namespace Sludio\HelperBundle\Script\Specification;

/**
 * And specification
 */
class AndSpecification extends CompositeSpecification
{
    /**
     * @var SpecificationInterface One specification
     */
    private $one;

    /**
     * @var SpecificationInterface Other specification
     */
    private $other;

    /**
     * Constructor
     *
     * @param SpecificationInterface $one
     * @param SpecificationInterface $other
     */
    public function __construct(SpecificationInterface $one, SpecificationInterface $other)
    {
        $this->one = $one;
        $this->other = $other;
    }

    /**
     * {@inheritdoc}
     */
    public function isSatisfiedBy($expectedValue, $actualValue = null)
    {
        return $this->one->isSatisfiedBy($expectedValue) && $this->other->isSatisfiedBy($expectedValue);
    }
}
