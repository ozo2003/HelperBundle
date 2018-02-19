<?php

namespace Sludio\HelperBundle\Flash\Model;

interface AlertManagerInterface
{
    public function addAlert(AlertInterface $alert);

    public function getAlerts();
}
