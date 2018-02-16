<?php

namespace Sludio\HelperBundle\Script\Model;

interface AlertManagerInterface
{
    public function addAlert(AlertInterface $alert);

    public function getAlerts();
}
