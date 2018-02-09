<?php

namespace Sludio\HelperBundle\DependencyInjection;

use Sludio\HelperBundle\DependencyInjection\Component\ConfigureInterface;
use Sludio\HelperBundle\DependencyInjection\Requirement\AbstractRequirement;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

if (!\defined('SLUDIO_HELPER')) {
    define('SLUDIO_HELPER', 'sludio_helper');
}

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class SludioHelperExtension extends Extension
{
    private static $files = [
        'components.yml',
        'parameters.yml',
        'services.yml',
    ];

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->loadConfig($configs, $container);

        foreach ($config['extensions'] as $key => $extension) {
            if (!isset($extension['enabled']) || $extension['enabled'] !== true) {
                continue;
            }
            if ($this->checkRequirements($key)) {
                /** @var $extension array */
                foreach ($extension as $variable => $value) {
                    if ($value === reset($extension)) {
                        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../'.ucfirst($key).'/Resources/config'));
                        foreach (self::$files as $file) {
                            if (file_exists(__DIR__.'/../'.ucfirst($key).'/Resources/config/'.$file)) {
                                $loader->load($file);
                            }
                        }
                    }
                    $container->setParameter($this->getAlias().'.'.$key.'.'.$variable, $value);
                }
                $this->checkComponent($key, $container, $this->getAlias());
            }
        }
    }

    private function loadConfig(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration($this->getAlias());
        /** @var $config array[] */
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        foreach (self::$files as $file) {
            if (file_exists(__DIR__.'/../Resources/config/'.$file)) {
                $loader->load($file);
            }
        }

        foreach ($config['other'] as $key => $other) {
            if (\is_array($other)) {
                foreach ($other as $variable => $value) {
                    $container->setParameter($this->getAlias().'.'.$key.'.'.$variable, $value);
                }
            } else {
                $container->setParameter($this->getAlias().'.'.$key, $other);
            }
        }

        return $config;
    }

    public function getAlias()
    {
        return \SLUDIO_HELPER;
    }

    private function checkRequirements($key)
    {
        $className = 'Sludio\\HelperBundle\\DependencyInjection\\Requirement\\'.ucfirst($key);
        if (class_exists($className) && method_exists($className, 'check')) {
            /** @var AbstractRequirement $class */
            $class = new $className();
            $class->check($key);
        }

        return true;
    }

    /**
     * @param                  $key
     * @param ContainerBuilder $container
     * @param                  $alias
     */
    private function checkComponent($key, ContainerBuilder $container, $alias)
    {
        $className = 'Sludio\\HelperBundle\\DependencyInjection\\Component\\'.ucfirst($key);
        if (class_exists($className) && method_exists($className, 'configure')) {
            $class = new $className();
            if ($class instanceof ConfigureInterface) {
                $class->configure($container, $alias);
            }
        }
    }
}
