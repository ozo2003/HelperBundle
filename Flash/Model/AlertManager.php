<?php

namespace Sludio\HelperBundle\Flash\Model;

use Symfony\Component\HttpFoundation\Session\Session;

class AlertManager implements AlertManagerInterface
{
    private $session;

    public function __construct(Session $session)
    {
        if (!$session->isStarted()) {
            $session->start();
        }
        $this->session = $session;
    }

    public function addAlert(AlertInterface $alert)
    {
        $this->session->getFlashBag()->add($alert->getType(), $alert->getMessage());
    }

    public function getAlerts()
    {
        $alerts = [];
        foreach (self::getAlertTypes() as $type) {
            $messages = $this->session->getFlashBag()->get($type);
            if (!empty($messages)) {
                $alerts = array_merge($alerts, $this->createAlertsForType($type, $messages));
            }
        }

        return $alerts;
    }

    private function createAlertsForType($type, array $messages)
    {
        $alerts = [];
        foreach ($messages as $msg) {
            $alerts[] = new Alert($type, $msg);
        }

        return $alerts;
    }

    public static function getAlertTypes()
    {
        return [
            AlertInterface::SUCCESS,
            AlertInterface::ERROR,
            AlertInterface::WARNING,
            AlertInterface::INFO,
        ];
    }
}
