<?php

namespace Sludio\HelperBundle\DependencyInjection;

use Sludio\HelperBundle\Guzzle\DataCollector\GuzzleCollector;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Sludio\HelperBundle\Flash\Helper\FlashAlertsHelper;

class Configuration implements ConfigurationInterface
{
    protected $alias;

    /**
     * Configuration constructor.
     *
     * @param $alias
     */
    public function __construct($alias)
    {
        $this->alias = $alias;
    }

    /**
     * {@inheritdoc}
     * @throws \RuntimeException
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root($this->alias);

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
                                                    return \constant('GuzzleHttp\MessageFormatter::'.strtoupper($v));
                                                })
                                            ->end()
                                            ->defaultValue('clf')
                                        ->end()
                                        ->scalarNode('level')
                                            ->beforeNormalization()
                                                ->ifInArray([
                                                    'emergency', 'alert', 'critical', 'error',
                                                    'warning', 'notice', 'info', 'debug',
                                                ])
                                                ->then(function($v) {
                                                    return \constant('Psr\Log\LogLevel::'.strtoupper($v));
                                                })
                                            ->end()
                                            ->defaultValue('debug')
                                        ->end()
                                    ->end()
                                ->end()
                                ->arrayNode('cache')
                                    ->canBeEnabled()
                                    ->validate()
                                        ->ifTrue(function($v) {
                                            return $v['enabled'] && null === $v['adapter'];
                                        })
                                        ->thenInvalid('The \''.$this->alias.'.guzzle.cache.adapter\' key is mandatory if you enable the cache middleware')
                                    ->end()
                                    ->children()
                                        ->scalarNode('adapter')->defaultValue(null)->end()
                                        ->booleanNode('disabled')->defaultValue(null)->end()
                                    ->end()
                                ->end()
                                ->arrayNode('clients')
                                    ->useAttributeAsKey('name')
                                    ->prototype('array')
                                        ->children()
                                            ->scalarNode('class')
                                                ->defaultValue('GuzzleHttp\Client')
                                            ->end()
                                            ->booleanNode('lazy')
                                                ->defaultValue(false)
                                            ->end()
                                            ->variableNode('config')->end()
                                            ->arrayNode('middleware')
                                                ->prototype('scalar')->end()
                                            ->end()
                                            ->scalarNode('alias')
                                                ->defaultValue(null)
                                            ->end()
                                            ->enumNode('authentication_type')
                                                ->values(['basic'])
                                                ->defaultValue('basic')
                                            ->end()
                                            ->arrayNode('credentials')
                                                ->canBeEnabled()
                                                ->addDefaultsIfNotSet()
                                                ->children()
                                                    ->scalarNode('user')
                                                        ->defaultValue(null)
                                                    ->end()
                                                    ->scalarNode('pass')
                                                        ->defaultValue(null)
                                                    ->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                                ->arrayNode('mock')
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
                                ->end()
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
                        ->arrayNode('mobile')
                            ->addDefaultsIfNotSet()
                            ->canBeEnabled()
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
                                ->arrayNode('entities')
                                    ->useAttributeAsKey('name')
                                    ->prototype('array')
                                        ->children()
                                            ->scalarNode('entity')
                                                ->defaultValue(null)
                                            ->end()
                                            ->arrayNode('fields')
                                                ->useAttributeAsKey('name')
                                                ->prototype('array')
                                                    ->children()
                                                        ->enumNode('class')
                                                            ->values(['titled', 'slugged', 'ck_item', null])
                                                            ->defaultValue(null)
                                                        ->end()
                                                        ->enumNode('type')
                                                            ->values(['text', 'ckeditor'])
                                                            ->defaultValue('text')
                                                        ->end()
                                                    ->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
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
}
