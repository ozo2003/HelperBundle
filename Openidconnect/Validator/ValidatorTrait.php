<?php

namespace Sludio\HelperBundle\Openidconnect\Validator;

trait ValidatorTrait
{
    protected $name;

    protected $message;

    protected $required;

    public function __construct($name, $required = false)
    {
        $this->name     = $name;
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
