<?php

namespace Sludio\HelperBundle\Captcha\Configurator;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ReCaptchaConfigurator implements CaptchaConfigurator
{

    public function buildConfiguration(NodeBuilder $node)
    {
        $node
            ->scalarNode('public_key')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('private_key')->isRequired()->cannotBeEmpty()->end()
            ->booleanNode('verify_host')->defaultValue(false)->end()
            ->booleanNode('ajax')->defaultValue(false)->end()
            ->scalarNode('locale_key')->defaultValue('en')->end()
            ->booleanNode('locale_from_request')->defaultValue(false)->end()
            ->scalarNode('template')->defaultValue('SludioHelperBundle:Captcha:sludio_helper_captcha_recaptcha_widget.html.twig')->end()
            ->scalarNode('resolver_class')->defaultValue('Sludio\HelperBundle\Captcha\Router\LocaleResolver')->end()
            ->scalarNode('type_class')->defaultValue('Sludio\HelperBundle\Captcha\Form\RecaptchaType')->end()
            ->scalarNode('validator_class')->defaultValue('Sludio\HelperBundle\Captcha\Validator\Constraint\IsTrueValidator')->end()
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
    
    public function configureClient(ContainerBuilder $container, $clientServiceKey, array $options = [])
    {
        /**/
        $resolver = $clientServiceKey.'.resolver';
        $resolverClass = $container->getParameter($clientServiceKey.'.resolver_class');
        
        $resolverDefinition = $container->register($resolver, $resolverClass);
        $resolverDefinition->setPublic(false);
        $resolverDefinition->setArguments([
            $container->getParameter($clientServiceKey.'.locale_key'),
            $container->getParameter($clientServiceKey.'.locale_from_request')
        ]);
        /**/
        $type = $clientServiceKey.'.form.type';
        $typeClass = $container->getParameter($clientServiceKey.'.type_class');
        $typeDefinition = $container->register($type, $typeClass);
        $typeDefinition->setArguments([
            $container->getParameter($clientServiceKey.'.public_key'),
            $container->getParameter($clientServiceKey.'.ajax'),
            $container->getDefinition($resolver)
        ]);
        $typeDefinition->addTag('form.type');
        /**/
        $validator = $clientServiceKey.'.validator.true';
        $validatorClass = $container->getParameter($clientServiceKey.'.validator_class');
        $validatorDefinition = $container->register($validator, $validatorClass);
        $validatorDefinition->setArguments([
            $container->getParameter($clientServiceKey.'.private_key'),
            $container->getParameter($clientServiceKey.'.http_proxy'),
            $container->getParameter($clientServiceKey.'.verify_host'),
        ]);
        $validatorDefinition->addTag('validator.constraint_validator');
        /**/
    }
}