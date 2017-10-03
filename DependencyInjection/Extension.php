<?php

namespace Sludio\HelperBundle\DependencyInjection;

use Sludio\HelperBundle\DependencyInjection\BaseExtension;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class Extension
{
    protected $ext;

    public function __construct($type)
    {
        $className = 'Sludio\\HelperBundle\\DependencyInjection\\BaseExtension\\'.ucfirst($type);
        $this->ext = class_exists($className) ? new $className() : null;
    }

    public function configure(ContainerBuilder &$container)
    {
        return $this->ext->configure($container);
    }

    public function getExtension()
    {
        return $this->ext;
    }
}
