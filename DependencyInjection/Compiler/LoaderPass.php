<?php

namespace Sludio\HelperBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class LoaderPass implements CompilerPassInterface
{
    const NAME = 'sludio_helper.guzzle';

    public function process(ContainerBuilder $container)
    {
        $ids = $container->findTaggedServiceIds(self::NAME.'.description_loader');

        if (!count($ids)) {
            return;
        }

        $resolverDefinition = $container->findDefinition(self::NAME.'.description_loader.resolver');

        $loaders = [];

        foreach ($ids as $id => $options) {
            $loaders[] = new Reference($id);
        }

        $resolverDefinition->setArguments([$loaders]);
    }
}