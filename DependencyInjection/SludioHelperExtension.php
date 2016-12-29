<?php

namespace Sludio\HelperBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

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

        foreach ($config['extensions'] as $key => $extension) {
            foreach ($extension as $var => $value) {
                if ($var == 'enabled' && $value) {
                    $files = array(
                        'services.yml', 'parameters.yml',
                    );
                    $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../'.ucfirst($key).'/Resources/config'));
                    foreach ($files as $file) {
                        if (file_exists(__DIR__.'/../'.ucfirst($key).'/Resources/config/'.$file)) {
                            $loader->load($file);
                        }
                    }
                } else {
                    if ($var != 'enabled') {
                        $container->setParameter('sludio_helper.'.$key.'.'.$var, $config['extensions'][$key][$var]);
                    }
                }
            }
        }

        foreach ($config['other'] as $key => $other) {
            foreach ($other as $var => $value) {
                $container->setParameter('sludio_helper.'.$key.'.'.$var, $config['other'][$key][$var]);
            }
        }
    }
}
