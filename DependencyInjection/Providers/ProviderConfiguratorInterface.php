<?php

namespace Sludio\HelperBundle\DependencyInjection\Providers;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;

interface ProviderConfiguratorInterface
{
    public function buildConfiguration(NodeBuilder $node);

    public function getProviderClass(array $configuration);

    public function getClientClass(array $config);

    public function getProviderOptions(array $configuration);

    public function getProviderDisplayName();
}
