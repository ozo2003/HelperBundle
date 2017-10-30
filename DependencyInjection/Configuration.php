<?php

namespace Sludio\HelperBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Sludio\HelperBundle\Guzzle\DataCollector\GuzzleCollector;
use GuzzleHttp\MessageFormatter;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sludio_helper');

        // @formatter:off
        $rootNode
            ->children()
                ->arrayNode('extensions')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('captcha')
                            ->addDefaultsIfNotSet()
                            ->canBeEnabled()
                            ->children()
                                ->arrayNode('clients')
                                    ->prototype('array')
                                        ->prototype('variable')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('guzzle')
                            ->addDefaultsIfNotSet()
                            ->canBeEnabled()
                            ->children()
                                ->arrayNode('profiler')
                                    ->canBeEnabled()
                                    ->children()
                                        ->integerNode('max_body_size')
                                            ->defaultValue(GuzzleCollector::MAX_BODY_SIZE)
                                        ->end()
                                    ->end()
                                ->end()
                                ->arrayNode('logger')
                                    ->canBeEnabled()
                                    ->children()
                                        ->scalarNode('service')->defaultValue(null)->end()
                                        ->scalarNode('format')
                                            ->beforeNormalization()
                                                ->ifInArray(['clf', 'debug', 'short'])
                                                ->then(function($v) {
                                                    return constant('GuzzleHttp\MessageFormatter::'.strtoupper($v));
                                                })
                                            ->end()
                                            ->defaultValue(MessageFormatter::CLF)
                                        ->end()
                                        ->scalarNode('level')
                                            ->beforeNormalization()
                                                ->ifInArray([
                                                    'emergency', 'alert', 'critical', 'error',
                                                    'warning', 'notice', 'info', 'debug',
                                                ])
                                                ->then(function($v) {
                                                    return constant('Psr\Log\LogLevel::'.strtoupper($v));
                                                })
                                            ->end()
                                            ->defaultValue('debug')
                                        ->end()
                                    ->end()
                                ->end()
                                ->append($this->createCacheNode())
                                ->append($this->createClientsNode())
                                ->append($this->createMockNode())
                            ->end()
                        ->end()
                        ->arrayNode('lexik')
                            ->addDefaultsIfNotSet()
                            ->canBeEnabled()
                            ->children()
                                ->scalarNode('default_domain')->defaultValue('messages')->end()
                                ->arrayNode('default_selections')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->booleanNode('non_translated_only')->defaultValue(false)->end()
                                    ->end()
                                ->end()
                                ->arrayNode('empty_prefixes')
                                    ->defaultValue(['__', 'new_', ''])
                                    ->prototype('array')->end()
                                ->end()
                                ->arrayNode('editable')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('type')->defaultValue('textarea')->end()
                                        ->scalarNode('emptytext')->defaultValue('Empty')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('oauth')
                            ->addDefaultsIfNotSet()
                            ->canBeEnabled()
                            ->children()
                                ->arrayNode('clients')
                                    ->prototype('array')
                                        ->prototype('variable')->end()
                                    ->end()
                                ->end()
                                ->arrayNode('custom_providers')
                                    ->prototype('array')
                                        ->prototype('variable')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('openid')
                            ->addDefaultsIfNotSet()
                            ->canBeEnabled()
                            ->children()
                                ->arrayNode('clients')
                                    ->prototype('array')
                                        ->prototype('variable')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('openidconnect')
                            ->addDefaultsIfNotSet()
                            ->canBeEnabled()
                            ->children()
                                ->arrayNode('clients')
                                    ->prototype('array')
                                        ->prototype('variable')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('pagination')
                            ->addDefaultsIfNotSet()
                            ->canBeEnabled()
                            ->children()
                                ->arrayNode('behaviour')
                                    ->prototype('array')
                                        ->prototype('variable')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('position')
                            ->addDefaultsIfNotSet()
                            ->canBeEnabled()
                            ->children()
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
                        ->arrayNode('script')
                            ->addDefaultsIfNotSet()
                            ->canBeEnabled()
                            ->children()
                                ->booleanNode('short_functions')
                                    ->defaultValue(false)
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('sitemap')
                            ->addDefaultsIfNotSet()
                            ->canBeEnabled()
                            ->children()
                                ->scalarNode('base_host')
                                    ->defaultValue(null)
                                ->end()
                                ->scalarNode('limit')
                                    ->defaultValue(null)
                                ->end()
                                ->enumNode('format')
                                    ->values(['simple', 'rich'])
                                    ->defaultValue('simple')
                                ->end()
                                ->enumNode('type')
                                    ->values(['simple', 'gz'])
                                    ->defaultValue('simple')
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('translatable')
                            ->addDefaultsIfNotSet()
                            ->canBeEnabled()
                            ->children()
                                ->arrayNode('locales')
                                    ->beforeNormalization()
                                        ->ifString()
                                            ->then(function($v) {
                                                return preg_split('/\s*,\s*/', $v);
                                            })
                                        ->end()
                                    ->prototype('scalar')->end()
                                ->end()
                                ->scalarNode('default_locale')
                                    ->defaultValue('en')
                                ->end()
                                ->scalarNode('template')
                                    ->defaultValue('SludioHelperBundle:Translatable:translations.html.twig')
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
                                ->scalarNode('guzzle')
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
                        ->scalarNode('locale')
                            ->defaultValue('en')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        // @formatter:on

        return $treeBuilder;
    }

    private function createCacheNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('cache');

        // @formatter:off
        $node
            ->canBeEnabled()
            ->validate()
                ->ifTrue(function($v) {
                    return $v['enabled'] && null === $v['adapter'];
                })
                ->thenInvalid('The \'sludio_helper.guzzle.cache.adapter\' key is mandatory if you enable the cache middleware')
            ->end()
            ->children()
                ->scalarNode('adapter')->defaultNull()->end()
                ->booleanNode('disabled')->defaultValue(null)->end()
            ->end()
        ;
        // @formatter:on

        return $node;
    }

    private function createClientsNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('clients');

        // @formatter:off
        $node
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->scalarNode('class')
                        ->defaultValue('GuzzleHttp\Client')
                    ->end()
                    ->booleanNode('lazy')
                        ->defaultFalse()
                    ->end()
                    ->variableNode('config')->end()
                    ->arrayNode('middleware')
                        ->prototype('scalar')->end()
                    ->end()
                    ->scalarNode('alias')->defaultNull()->end()
                ->end()
            ->end()
        ;
        // @formatter:on

        return $node;
    }

    private function createMockNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('mock');

        // @formatter:off
        $node
            ->canBeEnabled()
            ->children()
                ->scalarNode('storage_path')->isRequired()->end()
                ->scalarNode('mode')->defaultValue('replay')->end()
                ->arrayNode('request_headers_blacklist')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('response_headers_blacklist')
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;
        // @formatter:on

        return $node;
    }
}
