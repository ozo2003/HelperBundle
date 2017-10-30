<?php

namespace Sludio\HelperBundle\Script\Specification;

/**
 * Or specification
 */
class OrSpecification extends CompositeSpecification
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
    public function isSatisfiedBy($object)
    {
        return $this->one->isSatisfiedBy($object) || $this->other->isSatisfiedBy($object);
    }
}