<?php

namespace Sludio\HelperBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Reference;

use Sludio\HelperBundle\DependencyInjection\Providers\CustomProviderConfigurator;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class SludioHelperExtension extends Extension
{
    private $checkExternalClassExistence;
    
    private $configurators = [];
    
    private static $supportedProviderTypes = [
        'custom' => CustomProviderConfigurator::class,
    ];
    
    public function __construct($checkExternalClassExistence = true)
    {
        $this->checkExternalClassExistence = $checkExternalClassExistence;
    }
    
    public static function getAllSupportedTypes()
    {
        return array_keys(self::$supportedProviderTypes);
    }
    
    public function getConfigurator($type)
    {
        if (!isset($this->configurators[$type])) {
            $class = self::$supportedProviderTypes[$type];

            $this->configurators[$type] = new $class();
        }

        return $this->configurators[$type];
    }
    
    private function buildConfigurationForType(NodeDefinition $node, $type)
    {
        $optionsNode = $node->children();
        $optionsNode
            ->scalarNode('client_id')->isRequired()->end()
            ->scalarNode('client_secret')->isRequired()->end()
            ->scalarNode('redirect_route')->isRequired()->end()
            ->arrayNode('redirect_params')
                ->prototype('scalar')->end()
            ->end()
            ->booleanNode('use_state')->defaultValue(true)->end()
        ;

        $this->getConfigurator($type)
            ->buildConfiguration($optionsNode);
        $optionsNode->end();
    }
    
    public function getAlias()
    {
        return 'sludio_helper';
    }
    
    private function configureProviderAndClient(ContainerBuilder $container, $providerType, $providerKey, $providerClass, $clientClass, $packageName, array $options, $redirectRoute, array $redirectParams, $useState)
    {
        if ($this->checkExternalClassExistence && !class_exists($providerClass)) {
            throw new \LogicException(sprintf(
                'Run `composer require %s` in order to use the "%s" OAuth provider.',
                $packageName,
                $providerType
            ));
        }

        $providerServiceKey = sprintf('knpu.oauth2.provider.%s', $providerKey);

        $providerDefinition = $container->register(
            $providerServiceKey,
            $providerClass
        );
        $providerDefinition->setPublic(false);

        $providerDefinition->setFactory([
            new Reference('sludio_helper.oauth2.provider_factory'),
            'createProvider',
        ]);

        $providerDefinition->setArguments([
            $providerClass,
            $options,
            $redirectRoute,
            $redirectParams,
        ]);

        $clientServiceKey = sprintf('sludio_helper.oauth2.client.%s', $providerKey);
        $clientDefinition = $container->register(
            $clientServiceKey,
            $clientClass
        );
        $clientDefinition->setArguments([
            new Reference($providerServiceKey),
            new Reference('request_stack'),
        ]);

        if (!$useState) {
            $clientDefinition->addMethodCall('setAsStateless');
        }

        return $clientServiceKey;
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
        
        if ($container->getParameter('sludio_helper.oauth.enabled')) {
            $clientConfigurations = $container->getParameter('sludio_helper.oauth.clients');
            $clientServiceKeys = [];
            foreach ($clientConfigurations as $key => $clientConfig) {
                if (!isset($clientConfig['type'])) {
                    throw new InvalidConfigurationException(sprintf(
                       'Your "sludio_helper_oauth2_client.clients." config entry is missing the "type" key.',
                       $key
                   ));
                }
                $type = $clientConfig['type'];
                unset($clientConfig['type']);
                if (!isset(self::$supportedProviderTypes[$type])) {
                    throw new InvalidConfigurationException(sprintf(
                        'The "sludio_helper_oauth2_client.clients" config "type" key "%s" is not supported. We support (%s)',
                        $type,
                        implode(', ', self::$supportedProviderTypes)
                    ));
                }
                $tree = new TreeBuilder();
                $node = $tree->root('sludio_helper_oauth2_client/clients/' . $key);
                $this->buildConfigurationForType($node, $type);
                $processor = new Processor();
                $config = $processor->process($tree->buildTree(), [$clientConfig]);

                $configurator = $this->getConfigurator($type);
                $clientServiceKey = $this->configureProviderAndClient(
                    $container,
                    $type,
                    $key,
                    $configurator->getProviderClass($config),
                    $configurator->getClientClass($config),
                    $configurator->getPackagistName(),
                    $configurator->getProviderOptions($config),
                    $config['redirect_route'],
                    $config['redirect_params'],
                    $config['use_state']
                );
                
                $clientServiceKeys[$key] = $clientServiceKey;
            }
            
            $container->getDefinition('sludio_helper.oauth2.registry')->replaceArgument(1, $clientServiceKeys);
        }
    }
}
