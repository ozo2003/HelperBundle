<?php

namespace Sludio\HelperBundle\DependencyInjection\Configurator\Captcha;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;

class ReCaptchaConfigurator
{
    public function buildConfiguration(NodeBuilder $node)
    {
        $node
            ->scalarNode('public_key')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('private_key')->isRequired()->cannotBeEmpty()->end()
            ->booleanNode('verify_host')->defaultValue(false)->end()
            ->booleanNode('ajax')->defaultValue(false)->end()
            ->scalarNode('locale_key')->defaultValue('%kernel.default_locale%')->end()
            ->booleanNode('locale_from_request')->defaultValue(false)->end()
            ->scalarNode('template')->defaultValue('SludioHelperBundle:Captcha:sludio_helper.captcha.recaptcha.html.twig')->end()
            ->arrayNode('http_proxy')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('host')->defaultValue(null)->end()
                    ->scalarNode('port')->defaultValue(null)->end()
                    ->scalarNode('auth')->defaultValue(null)->end()
                ->end()
            ->end()
        ;
    }
}