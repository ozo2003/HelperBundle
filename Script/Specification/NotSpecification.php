<?php

namespace Sludio\HelperBundle\Script\Specification;

/**
 * Not specification
 */
class NotSpecification extends CompositeSpecification
{
    /**
     * @var SpecificationInterface
     */
    private $specification;

    /**
     * Constructor
     *
     * @param SpecificationInterface $specification
     */
    public function __construct(SpecificationInterface $specification)
    {
        $this->specification = $specification;
    }

    /**
     * {@inheritdoc}
     */
    public function isSatisfiedBy($expectedValue, $actualValue = null)
    {
        return !$this->specification->isSatisfiedBy($expectedValue);
    }
}
