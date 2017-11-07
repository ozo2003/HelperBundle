<?php

namespace Sludio\HelperBundle\Openidconnect\Specification;

trait SpecificationTrait
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var bool
     */
    protected $required;

    public function __construct($name, $required = false)
    {
        $this->name = $name;
        $this->required = $required;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function isRequired()
    {
        return $this->required;
    }
}
