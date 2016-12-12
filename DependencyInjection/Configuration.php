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
                ->arrayNode('locales')
                    ->beforeNormalization()
                        ->ifString()
                            ->then(function ($v) {
                                return preg_split('/\s*,\s*/', $v);
                            })
                    ->end()
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
                        ->booleanNode('gulp')->defaultValue(false)->end()
                        ->booleanNode('missing')->defaultValue(false)->end()
                        ->booleanNode('position')->defaultValue(false)->end()
                        ->booleanNode('sortable')->defaultValue(false)->end()
                        ->booleanNode('steam')->defaultValue(false)->end()
                        ->booleanNode('translatable')->defaultValue(false)->end()
                        ->booleanNode('usort')->defaultValue(false)->end()
                    ->end()
                ->end()
                ->arrayNode('redis')
                    ->beforeNormalization()
                        ->ifString()
                            ->then(function ($v) {
                                return preg_split('/\s*,\s*/', $v);
                            })
                    ->end()
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('translation_redis')->defaultValue('session')->end()
                ->scalarNode('em')->defaultValue('default')->end()
                ->scalarNode('steam_api_key')->defaultValue(null)->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
