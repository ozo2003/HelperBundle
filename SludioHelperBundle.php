<?php

namespace Sludio\HelperBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Sludio\HelperBundle\DependencyInjection\Compiler\TemplatingPass;

class SludioHelperBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new TemplatingPass());
    }
}
