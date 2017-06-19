<?php

namespace Sludio\HelperBundle\DependencyInjection\Providers;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;

class DraugiemProviderConfigurator implements ProviderConfiguratorInterface
{
    public function buildConfiguration(NodeBuilder $node)
    {
        $node
            ->scalarNode('client_class')
                ->info('If you have a sub-class of OAuth2Client you want to use, add it here')
                ->defaultValue('Sludio\HelperBundle\Oauth\Client\Client\DraugiemOAuth2Client')
            ->end()
            ->arrayNode('provider_options')
                ->info('Other options to pass to your provider\'s constructor')
                ->prototype('variable')->end()
            ->end()
        ;
    }

    public function getProviderClass(array $config)
    {
        return 'Sludio\HelperBundle\Oauth\Client\Provider\Draugiem\Draugiem';
    }

    public function getProviderOptions(array $config)
    {
        return
            array_merge(
                [
                    'clientId' => $config['client_id'],
                    'clientSecret' => $config['client_secret'],
                ],
                $config['provider_options']
            )
        ;
    }

    public function getProviderDisplayName()
    {
        return 'Draugiem.lv';
    }

    public function getClientClass(array $config)
    {
        return $config['client_class'];
    }
}
