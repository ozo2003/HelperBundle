<?php

namespace Sludio\HelperBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;
use Sludio\HelperBundle\DependencyInjection\Extension\BaseExtension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class SludioHelperExtension extends BaseExtension
{
    public function getAlias()
    {
        return 'sludio_helper';
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        foreach ($config['extensions'] as $key => $extension) {
            $enabled = false;
            foreach ($extension as $var => $value) {
                if ($var == 'enabled' && $value) {
                    $files = array(
                        'services.yml', 'parameters.yml', 'routing.yml',
                    );
                    $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../'.ucfirst($key).'/Resources/config'));
                    foreach ($files as $file) {
                        if (file_exists(__DIR__.'/../'.ucfirst($key).'/Resources/config/'.$file)) {
                            $loader->load($file);
                        }
                    }
                    $enabled = true;
                }
                if($enabled){
                    $container->setParameter('sludio_helper.'.$key.'.'.$var, $config['extensions'][$key][$var]);
                }
            }
        }

        foreach ($config['other'] as $key => $other) {
            foreach ($other as $var => $value) {
                $container->setParameter('sludio_helper.'.$key.'.'.$var, $config['other'][$key][$var]);
            }
        }

        if ($container->hasParameter('sludio_helper.oauth.enabled')) {
            $this->configureOAuth($container);
        }

        if ($container->hasParameter('sludio_helper.openid.enabled')) {
            $this->configureOpenID($container);
        }
    }
}
