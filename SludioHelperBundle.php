<?php

namespace Sludio\HelperBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Sludio\HelperBundle\DependencyInjection\Compiler\TemplatingPass;
use Sludio\HelperBundle\DependencyInjection\SludioHelperExtension;

class SludioHelperBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new TemplatingPass());
    }
    
    public function getContainerExtension()
    {
        if ($this->extension === null) {
            return new SludiohelperExtension();
        }
        
        return $this->extension;
    }
}
