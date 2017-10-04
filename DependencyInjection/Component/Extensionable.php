<?php

namespace Sludio\HelperBundle\DependencyInjection\Component;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

interface Extensionable
{
    public function configure(ContainerBuilder &$container);

    public function buildClientConfiguration(NodeDefinition &$node);

    public function configureClient(ContainerBuilder $container, $clientServiceKey, array $options = []);
}
