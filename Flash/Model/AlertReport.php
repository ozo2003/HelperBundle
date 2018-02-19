<?php

namespace Sludio\HelperBundle\Flash\Model;

class AlertReport implements AlertReporterInterface
{
    private $alertManager;

    public function __construct(AlertManagerInterface $alertManager)
    {
        $this->alertManager = $alertManager;
    }

    public function addError($message)
    {
        $this->add(AlertInterface::ERROR, $message);
    }

    public function addSuccess($message)
    {
        $this->add(AlertInterface::SUCCESS, $message);
    }

    public function addInfo($message)
    {
        $this->add(AlertInterface::INFO, $message);
    }

    public function addWarning($message)
    {
        $this->add(AlertInterface::WARNING, $message);
    }

    public function add($type = AlertInterface::INFO, $message)
    {
        if (!\in_array($type, AlertManager::getAlertTypes(), true)) {
            $type = AlertInterface::INFO;
        }
        $this->alertManager->addAlert(new Alert($type, $message));
    }
}
