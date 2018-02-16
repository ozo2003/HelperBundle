<?php

namespace Sludio\HelperBundle\Script\Model;

interface AlertInterface
{
    const SUCCESS = 'success';
    const ERROR = 'error';
    const WARNING = 'warning';
    const INFO = 'info';

    public function getType();

    public function getMessage();
}
