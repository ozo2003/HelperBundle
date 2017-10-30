<?php

namespace Sludio\HelperBundle\DependencyInjection\Component;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class Openidconnect implements Extension
{
    public function buildClientConfiguration(NodeDefinition &$node)
    {
        $optionsNode = $node->children();

        // @formatter:off
        $optionsNode
            ->scalarNode('client_key')->isRequired()->defaultNull()->end()
            ->scalarNode('client_secret')->defaultNull()->end()
            ->scalarNode('id_token_issuer')->isRequired()->defaultNull()->end()
            ->scalarNode('public_key')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('base_uri')->isRequired()->end()
            ->scalarNode('user_provider')->defaultValue('Sludio\HelperBundle\Openidconnect\Provider\OpenIDConnectProvider')->end()
            ->arrayNode('redirect')
                ->addDefaultsIfNotSet()
                ->children()
                    ->enumNode('type')
                        ->values(array('route', 'uri'))
                        ->defaultValue('route')
                    ->end()
                    ->scalarNode('route')->defaultNull()->end()
                    ->scalarNode('uri')->defaultNull()->end()
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

    private function buildUri(NodeDefinition &$node)
    {
        $optionsNode = $node->children();

        // @formatter:off
        $optionsNode
            ->arrayNode('params')->prototype('variable')->end()->end()
            ->arrayNode('url_params')->prototype('variable')->end()->end()
        ;
        // @formatter:on

        $optionsNode->end();
    }

    public function configureClient(ContainerBuilder $container, $clientServiceKey, array $options = [])
    {
        $clientDefinition = $container->register($clientServiceKey, $container->getParameter($clientServiceKey.'.user_provider'));
        $clientDefinition->setArguments([
            $clientServiceKey,
            $container->getParameter($clientServiceKey),
            [],
            new Reference('router'),
        ]);
    }

    public function configure(ContainerBuilder &$container)
    {
        $clientConfigurations = $container->getParameter('sludio_helper.openidconnect.clients');
        $clientServiceKeys = [];
        foreach ($clientConfigurations as $key => $clientConfig) {
            $tree = new TreeBuilder();
            $processor = new Processor();
            $node = $tree->root('sludio_helper_openidconnect_client/clients/'.$key);
            $this->buildClientConfiguration($node);
            $config = $processor->process($tree->buildTree(), [$clientConfig]);
            $clientServiceKey = 'sludio_helper.openidconnect.client.'.$key;
            $container->setParameter($clientServiceKey, $clientConfig);
            $service = [
                'key' => $clientServiceKey,
            ];
            if (isset($config['options']) && isset($config['options']['name'])) {
                $service['name'] = $config['options']['name'];
            } else {
                $service['name'] = ucfirst($key);
            }

            $clientServiceKeys[$key] = $service;
            foreach ($config as $configKey => $configValue) {
                if ('options' === $configKey) {
                    if (is_array($configValue)) {
                        foreach ($configValue as $parameterKey => $parameterValue) {
                            $container->setParameter($clientServiceKey.'.option.'.$parameterKey, $parameterValue);
                        }
                    }
                } else {
                    $container->setParameter($clientServiceKey.'.'.$configKey, $configValue);
                }
            }
            $uriConfigurations = $container->getParameter('sludio_helper.openidconnect.client.'.$key.'.uris');
            foreach ($uriConfigurations as $subKey => $uriConfig) {
                $tree = new TreeBuilder();
                $processor = new Processor();
                $node = $tree->root('sludio_helper_openidconnect_client/clients/'.$key.'/uris/'.$subKey);
                $this->buildUri($node);
                $config = $processor->process($tree->buildTree(), [$uriConfig]);
                $params = [];
                foreach ($config as $subConfigKey => $subConfigValue) {
                    if ($subConfigKey === 'params') {
                        if (is_array($subConfigValue)) {
                            foreach ($subConfigValue as $subParameterKey => $subParameterValue) {
                                $params[$subParameterKey] = $subParameterValue;
                            }
                            if (!empty($params)) {
                                $params['client_id'] = $container->getParameter('sludio_helper.openidconnect.client.'.$key.'.client_key');
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
        $container->getDefinition('sludio_helper.openidconnect.registry')->replaceArgument(1, $clientServiceKeys);
    }
}
