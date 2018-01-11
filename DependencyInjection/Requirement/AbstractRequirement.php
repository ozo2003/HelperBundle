<?php

namespace Sludio\HelperBundle\DependencyInjection\Requirement;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

abstract class AbstractRequirement
{
    public function check()
    {
        foreach ($this->getRequirements() as $class => $requirement) {
            if (!class_exists($class) && !interface_exists($class)) {
                throw new InvalidConfigurationException($requirement);
            }
        }
    }

    abstract public function getRequirements();
}
