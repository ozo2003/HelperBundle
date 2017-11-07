<?php

namespace Sludio\HelperBundle\Openidconnect\Specification;

trait SpecificationTrait
{
    protected $name;

    protected $message;

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
