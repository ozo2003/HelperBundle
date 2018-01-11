<?php

namespace Sludio\HelperBundle\Oauth\Configurator;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Sludio\HelperBundle\Oauth\Client\Provider\Twitter\Twitter;
use Sludio\HelperBundle\Oauth\Client\Client\TwitterOAuthClient;

class TwitterProviderConfigurator implements ProviderConfiguratorInterface
{
    public function buildConfiguration(NodeBuilder $node)
    {
        // @formatter:off
        $node
            ->scalarNode('client_class')
                ->info('If you have a sub-class of OAuth2Client you want to use, add it here')
                ->defaultValue(TwitterOAuthClient::class)
            ->end()
            ->scalarNode('redirect_route')
                ->isRequired()
                ->cannotBeEmpty()
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
        return Twitter::class;
    }

    public function getProviderOptions(array $config)
    {
        return array_merge([
            'clientId' => $config['client_id'],
            'clientSecret' => $config['client_secret'],
            'redirect_route' => $config['redirect_route'],
        ], $config['provider_options']);
    }

    public function getProviderDisplayName()
    {
        return 'Twitter';
    }

    public function getClientClass(array $config)
    {
        return $config['client_class'];
    }
}
