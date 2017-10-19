<?php

namespace Sludio\HelperBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class Extension
{
    protected $ext;

    public function __construct($type)
    {
        $className = 'Sludio\\HelperBundle\\DependencyInjection\\Component\\'.ucfirst($type);
        $this->setExt(class_exists($className) ? new $className() : null);
    }

    public function configure(ContainerBuilder &$container)
    {
        return $this->getExtension()->configure($container);
    }

    public function getExtension()
    {
        return $this->ext;
    }

    private function setExt($ext)
    {
        $this->ext = $ext;
    }
}