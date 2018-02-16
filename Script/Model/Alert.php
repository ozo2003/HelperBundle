<?php

namespace Sludio\HelperBundle\Script\Model;

class Alert implements AlertInterface
{
    private $type;
    private $message;

    public function __construct($type, $message)
    {
        $this->type = $type;
        $this->message = $message;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getMessage()
    {
        return $this->message;
    }
}
