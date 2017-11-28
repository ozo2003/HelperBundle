<?php

namespace Sludio\HelperBundle\DependencyInjection\Component;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

interface ExtensionInterface extends ConfigureInterface
{
    public function buildClientConfiguration(NodeDefinition $node);

    public function configureClient(ContainerBuilder $container, $clientServiceKey, array $options = []);
}
