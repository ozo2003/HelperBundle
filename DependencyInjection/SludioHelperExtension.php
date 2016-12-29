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
        $loader->load('parameters.yml');

        foreach ($config['extensions'] as $key => $extension) {
            if ($extension) {
                $files = array(
                    'services.yml', 'parameters.yml',
                );
                $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../'.ucfirst($key).'/Resources/config'));
                foreach ($files as $file) {
                    if (file_exists(__DIR__.'/../'.ucfirst($key).'/Resources/config/'.$file)) {
                        $loader->load($file);
                    }
                }
            }
        }

        if ($config['extensions']['position']) {
            $container->setParameter('sludio_helper.position.position.field', $config['position_field']);
            $positionHandler = 'sludio_helper.position.orm';
            $container->setAlias('sludio_helper.position', new Alias($positionHandler));
        }

        if ($config['extensions']['steam']) {
            $container->setParameter('sludio_helper.steam.api_key', $config['steam_api_key']);
        }

        if ($config['extensions']['translatable']) {
            $container->setParameter('sludio_helper.locales', $config['locales']);
            $container->setParameter('sludio_helper.template', $config['template']);
        }

        $container->setParameter('sludio_helper.redis', $config['redis']);
        $container->setParameter('sludio_helper.translation_redis', 'snc_redis.'.$config['translation_redis']);

        $container->setParameter('sludio_helper.entity_manager', $config['em']);
    }
}
