<?php

namespace Sludio\HelperBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Alias;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class SludioHelperExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        
        $container->setParameter('sludio_helper.locales', $config['locales']);
        $container->setParameter('sludio_helper.template', $config['template']);
        
        if($config['extensions']['beautify']){
            $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Beautify/Resources/config'));
            $loader->load('services.yml');
        }
        
        if($config['extensions']['browser']){
            $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Browser/Resources/config'));
            $loader->load('services.yml');
        }
        
        if($config['extensions']['missing']){
            $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Missing/Resources/config'));
            $loader->load('services.yml');
        }
        
        if($config['extensions']['position']){
            $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Position/Resources/config'));
            $loader->load('services.yml');
            
            $container->setParameter('sludio_helper.position.position.field', $config['position_field']);
            $positionHandler = 'sludio_helper.position.orm';
            $container->setAlias('sludio_helper.position', new Alias($positionHandler));
        }
        
        if($config['extensions']['translatable']){
            $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Translatable/Resources/config'));
            $loader->load('services.yml');
        }
    }
}
