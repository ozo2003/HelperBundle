<?php

namespace Sludio\HelperBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class UrlProviderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('sludio_helper.sitemap.enabled')) {
            return;
        }
        $format = $container->getParameter('sludio_helper.sitemap.format');
        $type = $container->getParameter('sludio_helper.sitemap.type');

        $definition = $container->getDefinition("sludio_helper.sitemap.{$format}.{$type}");

        foreach ($container->findTaggedServiceIds('sludio_helper.sitemap.provider') as $id => $attributes) {
            $definition->addMethodCall('addProvider', [new Reference($id)]);
        }
    }
}
