<?php

namespace Sludio\HelperBundle\DependencyInjection\Component;

use Symfony\Component\DependencyInjection\ContainerBuilder;

interface Configurable
{
    public function configure(ContainerBuilder &$container, $alias);
}