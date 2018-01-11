<?php

namespace Sludio\HelperBundle\Oauth\Configurator;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Sludio\HelperBundle\Oauth\Client\OAuth2Client;
use Sludio\HelperBundle\Oauth\Client\Provider\Custom\Custom;

class CustomProviderConfigurator implements ProviderConfiguratorInterface
{
    public function buildConfiguration(NodeBuilder $node)
    {
        // @formatter:off
        $node
            ->scalarNode('provider_class')
                ->info('The class name of your provider class (e.g. the one that extends AbstractProvider)')
                ->defaultValue(Custom::class)
            ->end()
            ->scalarNode('client_class')
                ->info('If you have a sub-class of OAuth2Client you want to use, add it here')
                ->defaultValue(OAuth2Client::class)
            ->end()
            ->arrayNode('provider_options')
                ->info('Other options to pass to your provider\'s constructor')
                ->prototype('variable')->end()
            ->end()
        ;
        // @formatter:on
    }

    public function getProviderClass(array $config)
    {
        return $config['provider_class'];
    }

    public function getProviderOptions(array $config)
    {
        return array_merge([
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
        ], $config['provider_options']);
    }

    public function getProviderDisplayName()
    {
        return 'Custom';
    }

    public function getClientClass(array $config)
    {
        return $config['client_class'];
    }
}
