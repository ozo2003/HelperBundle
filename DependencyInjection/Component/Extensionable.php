<?php

namespace Sludio\HelperBundle\DependencyInjection\Component;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

interface Extensionable
{
    public function configure(ContainerBuilder &$container);

    public function buildClientConfiguration(NodeDefinition &$node);

    public function configureClient(ContainerBuilder $container, $clientServiceKey, array $options = []);
}
