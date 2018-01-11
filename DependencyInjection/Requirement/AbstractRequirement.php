<?php

namespace Sludio\HelperBundle\DependencyInjection\Requirement;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

abstract class AbstractRequirement
{
    protected $key;

    public function check($key)
    {
        $this->key = $key;

        foreach ($this->getRequirements() as $class => $requirement) {
            if (!class_exists($class) && !interface_exists($class)) {
                throw new InvalidConfigurationException($this->throwException());
            }
        }
    }

    abstract public function getRequirements();

    public function throwException()
    {
        $string = 'In order to use "'.$this->key.'" extension, these packages must be installed:'."\n\n";
        foreach ($this->getRequirements() as $class => $requirement) {
            $string .= $requirement."\n";
        }

        $string .= "\n".'Packages can be installed by running this command:'."\n";
        $string .= 'composer require '.implode(' ', $this->getRequirements());

        return $string;
    }
}
