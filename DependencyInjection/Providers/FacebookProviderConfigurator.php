<?php

namespace Sludio\HelperBundle\DependencyInjection\Providers;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;

class FacebookProviderConfigurator implements ProviderConfiguratorInterface
{
    public function buildConfiguration(NodeBuilder $node)
    {
        $node
            ->scalarNode('graph_api_version')
                ->isRequired()
                ->defaultValue('v2.4')
            ->end()
            ->scalarNode('client_class')
                ->info('If you have a sub-class of OAuth2Client you want to use, add it here')
                ->defaultValue('Sludio\HelperBundle\Oauth\Client\OAuth2Client')
            ->end()
            ->arrayNode('provider_options')
                ->info('Other options to pass to your provider\'s constructor')
                ->prototype('variable')->end()
            ->end()
        ;
    }

    public function getProviderClass(array $config)
    {
        return 'Sludio\HelperBundle\Oauth\Client\Provider\Facebook\Facebook';
    }

    public function getProviderOptions(array $config)
    {
        return
            array_merge(
                [
                    'clientId' => $config['client_id'],
                    'clientSecret' => $config['client_secret'],
                    'graphApiVersion' => $config['graph_api_version'],
                ],
                $config['provider_options']
            )
        ;
    }

    public function getPackagistName()
    {
        return '';
    }

    public function getLibraryHomepage()
    {
        return '';
    }

    public function getProviderDisplayName()
    {
        return 'Facebook';
    }

    public function getClientClass(array $config)
    {
        return $config['client_class'];
    }
}
