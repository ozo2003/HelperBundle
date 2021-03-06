<?php

namespace Sludio\HelperBundle\Oauth\Configurator;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Sludio\HelperBundle\Oauth\Client\Provider\Google\Google;
use Sludio\HelperBundle\Oauth\Client\Client\GoogleOAuth2Client;

class GoogleProviderConfigurator implements ProviderConfiguratorInterface
{
    public function buildConfiguration(NodeBuilder $node)
    {
        // @formatter:off
        $node
            ->scalarNode('access_type')
                ->defaultValue(null)
                ->info('Optional value for sending access_type parameter. More detail: https://developers.google.com/identity/protocols/OpenIDConnect#authenticationuriparameters')
            ->end()
            ->scalarNode('hosted_domain')
                ->defaultValue(null)
                ->info('Optional value for sending hd parameter. More detail: https://developers.google.com/identity/protocols/OpenIDConnect#hd-param')
            ->end()
            ->arrayNode('user_fields')
                ->prototype('scalar')->end()
                ->info('Optional value for additional fields to be requested from the user profile. If set, these values will be included with the defaults. More details: https://developers.google.com/+/web/api/rest/latest/people')
            ->end()
            ->scalarNode('client_class')
                ->info('If you have a sub-class of OAuth2Client you want to use, add it here')
                ->defaultValue(GoogleOAuth2Client::class)
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
        return Google::class;
    }

    public function getProviderOptions(array $config)
    {
        $options = [
            'clientId' => $config['client_id'],
            'clientSecret' => $config['client_secret'],
            'redirect_route' => $config['redirect_route'],
        ];

        if ($config['access_type']) {
            $options['accessType'] = $config['access_type'];
        }

        if ($config['hosted_domain']) {
            $options['hostedDomain'] = $config['hosted_domain'];
        }

        if (!empty($config['user_fields'])) {
            $options['userFields'] = $config['user_fields'];
        }

        return array_merge($options, $config['provider_options']);
    }

    public function getProviderDisplayName()
    {
        return 'Google';
    }

    public function getClientClass(array $config)
    {
        return $config['client_class'];
    }
}
