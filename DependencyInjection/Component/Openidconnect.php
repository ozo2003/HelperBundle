<?php

namespace Sludio\HelperBundle\DependencyInjection\Component;

use Sludio\HelperBundle\Openidconnect\Provider\OpenIDConnectProvider;
use Sludio\HelperBundle\Openidconnect\Provider\BaseProvider;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class Openidconnect implements ExtensionInterface
{
    protected $alias;

    public function buildClientConfiguration(NodeDefinition $node)
    {
        $node->addDefaultsIfNotSet();
        $optionsNode = $node->children();

        // @formatter:off
        $optionsNode
            ->scalarNode('client_key')->isRequired()->defaultValue(null)->end()
            ->scalarNode('client_secret')->defaultValue(null)->end()
            ->scalarNode('id_token_issuer')->isRequired()->defaultValue(null)->end()
            ->scalarNode('public_key')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('base_uri')->isRequired()->end()
            ->scalarNode('user_provider')->defaultValue(BaseProvider::class)->end()
            ->scalarNode('use_session')->defaultValue(false)->end()
            ->arrayNode('redirect')
                ->addDefaultsIfNotSet()
                ->children()
                    ->enumNode('type')
                        ->values(array('route', 'uri'))
                        ->defaultValue('route')
                    ->end()
                    ->scalarNode('route')->defaultValue(null)->end()
                    ->scalarNode('uri')->defaultValue(null)->end()
                    ->arrayNode('params')->prototype('variable')->end()->end()
                ->end()
            ->end()
            ->arrayNode('uris')
                ->prototype('array')
                    ->prototype('variable')->end()
                ->end()
            ->end()
        ;
        // @formatter:on

        $optionsNode->end();
    }

    private function buildUri(NodeDefinition $node)
    {
        $node->addDefaultsIfNotSet();
        $optionsNode = $node->children();

        // @formatter:off
        $optionsNode
            ->arrayNode('params')
                ->prototype('variable')->end()
            ->end()
            ->arrayNode('url_params')
                ->prototype('variable')->end()
            ->end()
            ->enumNode('method')->values(array(OpenIDConnectProvider::METHOD_GET, OpenIDConnectProvider::METHOD_POST))->cannotBeEmpty()->end()
        ;
        // @formatter:on

        $optionsNode->end();
    }

    public function configureClient(ContainerBuilder $container, $clientServiceKey, array $options = [])
    {
        $clientDefinition = $container->register($clientServiceKey, $container->getParameter($clientServiceKey.'.user_provider'));
        $clientDefinition->setArguments([
            $container->getParameter($clientServiceKey),
            [],
            new Reference('router'),
            new Reference('session')
        ]);
    }

    public function configure(ContainerBuilder $container, $alias)
    {
        $this->alias = $alias.'.openidconnect';
        $clientConfigurations = $container->getParameter($this->alias.'.clients');
        $clientServiceKeys = [];
        /** @var $clientConfigurations array */
        foreach ($clientConfigurations as $key => $clientConfig) {
            $tree = new TreeBuilder();
            $processor = new Processor();
            $node = $tree->root('sludio_helper_openidconnect_client/clients/'.$key);
            $this->buildClientConfiguration($node);
            /** @var array $config */
            $config = $processor->process($tree->buildTree(), [$clientConfig]);
            $clientServiceKey = $this->alias.'.client.'.$key;
            $container->setParameter($clientServiceKey, $clientConfig);
            $service = [
                'key' => $clientServiceKey,
            ];
            if (isset($config['options']['name'])) {
                $service['name'] = $config['options']['name'];
            } else {
                $service['name'] = ucfirst($key);
            }

            $clientServiceKeys[$key] = $service;
            foreach ($config as $configKey => $configValue) {
                if ('options' === $configKey) {
                    if (\is_array($configValue)) {
                        foreach ($configValue as $parameterKey => $parameterValue) {
                            $container->setParameter($clientServiceKey.'.option.'.$parameterKey, $parameterValue);
                        }
                    }
                } else {
                    $container->setParameter($clientServiceKey.'.'.$configKey, $configValue);
                }
            }
            $uriConfigurations = $container->getParameter($this->alias.'.client.'.$key.'.uris');
            /** @var $uriConfigurations array */
            foreach ($uriConfigurations as $subKey => $uriConfig) {
                $tree = new TreeBuilder();
                $processor = new Processor();
                $node = $tree->root('sludio_helper_openidconnect_client/clients/'.$key.'/uris/'.$subKey);
                $this->buildUri($node);
                $config = $processor->process($tree->buildTree(), [$uriConfig]);
                $params = [];
                foreach ($config as $subConfigKey => $subConfigValue) {
                    if ($subConfigKey === 'params') {
                        if (\is_array($subConfigValue)) {
                            foreach ($subConfigValue as $subParameterKey => $subParameterValue) {
                                $params[$subParameterKey] = $subParameterValue;
                            }
                            if (!empty($params)) {
                                $params['client_id'] = $container->getParameter($this->alias.'.client.'.$key.'.client_key');
                                $container->setParameter($clientServiceKey.'.'.$subKey.'.'.$subConfigKey, $params);
                            }
                        }
                    } else {
                        $container->setParameter($clientServiceKey.'.'.$subKey.'.'.$subConfigKey, $subConfigValue);
                    }
                }
            }
            $this->configureClient($container, $clientServiceKey);
        }
        $container->getDefinition($this->alias.'.registry')->replaceArgument(1, $clientServiceKeys);
    }
}
