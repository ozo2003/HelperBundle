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
            ->fixXmlConfig('extension')
            ->children()
                ->arrayNode('extensions')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('oauth')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')
                                    ->defaultValue(false)
                                ->end()
                                ->arrayNode('clients')
                                    ->prototype('array')
                                        ->prototype('variable')->end()
                                    ->end()
                                ->end()
                                ->scalarNode('server_manager')
                                    ->defaultValue('fos_oauth_server.client_manager.default')
                                ->end()
                                ->arrayNode('tables')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('access')
                                            ->defaultValue('oauth_access_token')
                                        ->end()
                                        ->scalarNode('refresh')
                                            ->defaultValue('oauth_refresh_token')
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('openid')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')
                                    ->defaultValue(false)
                                ->end()
                                ->arrayNode('clients')
                                    ->prototype('array')
                                        ->prototype('variable')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('openidconnect')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')
                                    ->defaultValue(false)
                                ->end()
                                ->arrayNode('clients')
                                    ->prototype('array')
                                        ->prototype('variable')->end()
                                    ->end()
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
                        ->arrayNode('scripts')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')
                                    ->defaultValue(false)
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
                                ->scalarNode('template_new')
                                    ->defaultValue('SludioHelperBundle:Translatable:translations_new.html.twig')
                                ->end()
                                ->scalarNode('table')
                                    ->defaultValue('sludio_helper_translation')
                                ->end()
                                ->scalarNode('manager')
                                    ->defaultValue('default')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('other')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('logger')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('class')
                                    ->defaultValue('Sludio\HelperBundle\Logger\SludioLogger')
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('redis')
                            ->addDefaultsIfNotSet()
                            ->children()
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
