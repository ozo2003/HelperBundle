<?php

namespace Sludio\HelperBundle\DependencyInjection\Component;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class Openid implements Extension
{
    protected $alias;

    public function configure(ContainerBuilder &$container, $alias)
    {
        $this->alias = $alias.'.openid';
        $clientConfigurations = $container->getParameter($this->alias.'.clients');
        $clientServiceKeys = [];
        foreach ($clientConfigurations as $key => $clientConfig) {
            $tree = new TreeBuilder();
            $node = $tree->root('sludio_helper_openid_client/clients/'.$key);
            $this->buildClientConfiguration($node);
            $processor = new Processor();
            $config = $processor->process($tree->buildTree(), [$clientConfig]);
            $clientServiceKey = $this->alias.'.client.'.$key;
            $service = [
                'key' => $clientServiceKey,
            ];
            if (isset($config['provider_options']) && isset($config['provider_options']['name'])) {
                $service['name'] = $config['provider_options']['name'];
            } else {
                $service['name'] = ucfirst($key);
            }

            $clientServiceKeys[$key] = $service;
            foreach ($config as $ckey => $cvalue) {
                if ($ckey === 'provider_options') {
                    if (is_array($cvalue)) {
                        foreach ($cvalue as $pkey => $pvalue) {
                            $container->setParameter($clientServiceKey.'.option.'.$pkey, $pvalue);
                        }
                    }
                } else {
                    $container->setParameter($clientServiceKey.'.'.$ckey, $cvalue);
                }
            }
            $this->configureClient($container, $clientServiceKey);
        }
        $container->getDefinition($this->alias.'.registry')->replaceArgument(1, $clientServiceKeys);
        if ($container->getParameter($alias.'.oauth.enabled') == true) {
            $container->getDefinition($alias.'.registry')->replaceArgument(2, $clientServiceKeys);
        }
    }

    public function buildClientConfiguration(NodeDefinition &$node)
    {
        $optionsNode = $node->children();

        // @formatter:off
        $optionsNode
            ->scalarNode('api_key')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('openid_url')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('preg_check')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('ns_mode')->defaultValue('sreg')->end()
            ->scalarNode('user_class')->isRequired()->end()
            ->scalarNode('user_provider')->defaultValue('Sludio\HelperBundle\Openid\Login\Login')->end()
            ->scalarNode('redirect_route')->isRequired()->cannotBeEmpty()->end()
            ->arrayNode('provider_options')->prototype('variable')->end()->end()
        ;
        // @formatter:on

        $optionsNode->end();
    }

    public function configureClient(ContainerBuilder $container, $clientServiceKey, array $options = [])
    {
        $clientDefinition = $container->register($clientServiceKey, $container->getParameter($clientServiceKey.'.user_provider'));
        $clientDefinition->setArguments([
            $clientServiceKey,
            new Reference('request_stack'),
            new Reference('service_container'),
            new Reference('router'),
        ]);
    }
}
