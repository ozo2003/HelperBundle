<?php

namespace Sludio\HelperBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class SludioHelperExtension extends Extension
{

    public function getAlias()
    {
        return 'sludio_helper';
    }

    private function checkExtension($key)
    {
        $className = 'Sludio\\HelperBundle\\DependencyInjection\\Component\\'.ucfirst($key);
        if (class_exists($className)) {
            return new $className();
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $files = [
            'components.yml',
            'parameters.yml',
            'services.yml',
        ];
        foreach ($files as $file) {
            if (file_exists(__DIR__.'/../Resources/config/'.$file)) {
                $loader->load($file);
            }
        }

        foreach ($config['extensions'] as $key => $extension) {
            $enabled = $iterator = 0;
            $length = count($extension);
            foreach ($extension as $variable => $value) {
                $iterator++;
                if ($variable == 'enabled' && $value) {
                    $files = [
                        'components.yml',
                        'parameters.yml',
                        'services.yml',
                    ];
                    $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../'.ucfirst($key).'/Resources/config'));
                    foreach ($files as $file) {
                        if (file_exists(__DIR__.'/../'.ucfirst($key).'/Resources/config/'.$file)) {
                            $loader->load($file);
                        }
                    }
                    $enabled = 1;
                }
                if ($enabled === 1) {
                    $container->setParameter('sludio_helper.'.$key.'.'.$variable, $config['extensions'][$key][$variable]);
                }
                if ($iterator === $length && $enabled === 1) {
                    if ($ext = $this->checkExtension($key)) {
                        $ext->configure($container);
                    }
                }
            }
        }

        foreach ($config['other'] as $key => $other) {
            if (is_array($other)) {
                foreach ($other as $variable => $value) {
                    $container->setParameter('sludio_helper.'.$key.'.'.$variable, $config['other'][$key][$variable]);
                }
            } else {
                $container->setParameter('sludio_helper.'.$key, $config['other'][$key]);
            }
        }
    }
}