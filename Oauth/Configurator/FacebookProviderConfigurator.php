<?php

namespace Sludio\HelperBundle\Oauth\Configurator;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Sludio\HelperBundle\Oauth\Client\Provider\Facebook\Facebook;
use Sludio\HelperBundle\Oauth\Client\Client\FacebookOAuthClient;

class FacebookProviderConfigurator implements ProviderConfiguratorInterface
{
    public function buildConfiguration(NodeBuilder $node)
    {
        // @formatter:off
        $node
            ->scalarNode('graph_api_version')
                ->isRequired()
                ->defaultValue('v2.8')
            ->end()
            ->arrayNode('fields')
                ->beforeNormalization()
                    ->ifString()
                        ->then(function($v) {
                            return preg_split('/\s*,\s*/', $v);
                        })
                    ->end()
                ->prototype('scalar')->end()
                ->validate()
                    ->ifTrue(function($value) {
                        $fields = \explode(';', Facebook::FIELDS);
                        foreach ($value as $v) {
                            foreach (preg_split('/\s*,\s*/', $v) as $item) {
                                if (!\in_array($item, $fields)) {
                                    return true;
                                }
                            }
                        }
                    })
                    ->thenInvalid('Unsupported field. Supported fields: '.Facebook::FIELDS)
                ->end()
                ->defaultValue([])
            ->end()
            ->scalarNode('client_class')
                ->info('If you have a sub-class of OAuth2Client you want to use, add it here')
                ->defaultValue(FacebookOAuthClient::class)
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
        return Facebook::class;
    }

    public function getProviderOptions(array $config)
    {
        return array_merge([
            'clientId' => $config['client_id'],
            'clientSecret' => $config['client_secret'],
            'graphApiVersion' => $config['graph_api_version'],
            'redirect_route' => $config['redirect_route'],
            'fields' => $config['fields'],
        ], $config['provider_options']);
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
