<?php

namespace Sludio\HelperBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sludio_helper');

        $rootNode
            ->children()
                ->arrayNode('locales')
                    ->beforeNormalization()
                        ->ifString()
                            ->then(function ($v) {
                                return preg_split('/\s*,\s*/', $v);
                            })
                    ->end()
                    ->requiresAtLeastOneElement()
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('template')->defaultValue('SludioHelperBundle:Translatable:translations.html.twig')->end()
                ->arrayNode('position_field')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default')
                            ->defaultValue('position')
                        ->end()
                        ->arrayNode('entities')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('extensions')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('beautify')->defaultValue(false)->end()
                        ->booleanNode('browser')->defaultValue(false)->end()
                        ->booleanNode('missing')->defaultValue(false)->end()
                        ->booleanNode('position')->defaultValue(false)->end()
                        ->booleanNode('translatable')->defaultValue(false)->end()
                    ->end()
                ->end()
                ->arrayNode('redis')
                    ->addDefaultsIfNotSet()
                    ->prototype('scalar')
                    
            ->end()
        ;

        return $treeBuilder;
    }
}
