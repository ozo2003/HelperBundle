<?php

namespace Sludio\HelperBundle\DependencyInjection\Requirement;

abstract class AbstractRequirement
{
    public function check()
    {
        foreach ($this->getRequirements() as $class => $requirement) {
            if (!class_exists($class)) {
                throw new InvalidConfigurationException($requirement);
            }
        }
    }

    abstract public function getRequirements();
}
