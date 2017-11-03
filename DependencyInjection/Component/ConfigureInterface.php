<?php

namespace Sludio\HelperBundle\DependencyInjection\Component;

use Symfony\Component\DependencyInjection\ContainerBuilder;

interface ConfigureInterface
{
    public function configure(ContainerBuilder &$container, $alias);
}