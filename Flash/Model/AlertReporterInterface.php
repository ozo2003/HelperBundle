<?php

namespace Sludio\HelperBundle\Flash\Model;

interface AlertReporterInterface
{
    public function addError($message);

    public function addSuccess($message);

    public function addInfo($message);

    public function addWarning($message);

    public function add($type = 'info', $message);
}
