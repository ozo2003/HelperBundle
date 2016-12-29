<?php

namespace Sludio\HelperBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

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
                ->arrayNode('extensions')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('beautify')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')
                                    ->defaultValue(false)
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('browser')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')
                                    ->defaultValue(false)
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('gulp')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')
                                    ->defaultValue(false)
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('missing')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')
                                    ->defaultValue(false)
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('pagination')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')
                                    ->defaultValue(false)
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('position')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')
                                    ->defaultValue(false)
                                ->end()
                                ->arrayNode('field')
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
                            ->end()
                        ->end()
                        ->arrayNode('sortable')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')
                                    ->defaultValue(false)
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('steam')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')
                                    ->defaultValue(false)
                                ->end()
                                ->scalarNode('api_key')
                                    ->defaultValue(null)
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('translatable')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')
                                    ->defaultValue(false)
                                ->end()
                                ->arrayNode('locales')
                                    ->beforeNormalization()
                                        ->ifString()
                                            ->then(function ($v) {
                                                return preg_split('/\s*,\s*/', $v);
                                            })
                                        ->end()
                                    ->prototype('scalar')->end()
                                ->end()
                                ->scalarNode('template')
                                    ->defaultValue('SludioHelperBundle:Translatable:translations.html.twig')
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('usort')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')
                                    ->defaultValue(false)
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('other')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('redis')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('managers')
                                    ->beforeNormalization()
                                        ->ifString()
                                            ->then(function ($v) {
                                                return preg_split('/\s*,\s*/', $v);
                                            })
                                    ->end()
                                    ->prototype('scalar')->end()
                                ->end()
                                ->scalarNode('translation')
                                    ->defaultValue('session')
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('entity')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('manager')
                                    ->defaultValue('default')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
