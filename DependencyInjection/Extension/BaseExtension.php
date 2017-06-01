<?php

namespace Sludio\HelperBundle\DependencyInjection\Extension;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Reference;

use Sludio\HelperBundle\DependencyInjection\Providers\CustomProviderConfigurator;
use Sludio\HelperBundle\DependencyInjection\Providers\FacebookProviderConfigurator;
use Sludio\HelperBundle\DependencyInjection\Providers\GoogleProviderConfigurator;

abstract class BaseExtension extends Extension
{
    protected $checkExternalClassExistence;

    protected $configurators = [];

    protected static $supportedProviderTypes = [
        'custom' => CustomProviderConfigurator::class,
        'facebook' => FacebookProviderConfigurator::class,
        'google' => GoogleProviderConfigurator::class,
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
            ->booleanNode('use_state')->defaultValue(true)->end()
        ;

        $this->getConfigurator($type)
            ->buildConfiguration($optionsNode);
        $optionsNode->end();
    }

    private function configureProviderAndClient(ContainerBuilder $container, $providerType, $providerKey, $providerClass, $clientClass, $packageName, array $options, $useState)
    {
        if ($this->checkExternalClassExistence && !class_exists($providerClass)) {
            throw new \LogicException(sprintf(
                'Run `composer require %s` in order to use the "%s" OAuth provider.',
                $packageName,
                $providerType
            ));
        }

        $providerServiceKey = sprintf('sludio_helper.oauth.provider.%s', $providerKey);

        $providerDefinition = $container->register(
            $providerServiceKey,
            $providerClass
        );
        $providerDefinition->setPublic(false);

        $providerDefinition->setFactory([
            new Reference('sludio_helper.oauth.provider_factory'),
            'createProvider',
        ]);

        $providerDefinition->setArguments([
            $providerClass,
            $options,
            $options['redirect_route']
        ]);

        $clientServiceKey = sprintf('sludio_helper.oauth.client.%s', $providerKey);
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

    public function configureOAuth(ContainerBuilder &$container)
    {
        $clientConfigurations = $container->getParameter('sludio_helper.oauth.clients');
        $clientServiceKeys = [];
        foreach ($clientConfigurations as $key => $clientConfig) {
            if (!isset($clientConfig['type'])) {
                throw new InvalidConfigurationException(sprintf(
                   'Your "sludio_helper_oauth_client.clients." config entry is missing the "type" key.',
                   $key
               ));
            }
            $type = $clientConfig['type'];
            unset($clientConfig['type']);
            if (!isset(self::$supportedProviderTypes[$type])) {
                throw new InvalidConfigurationException(sprintf(
                    'The "sludio_helper_oauth_client.clients" config "type" key "%s" is not supported. We support (%s)',
                    $type,
                    implode(', ', self::$supportedProviderTypes)
                ));
            }
            $tree = new TreeBuilder();
            $node = $tree->root('sludio_helper_oauth_client/clients/' . $key);
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
                $config['use_state']
            );

            $clientServiceKeys[$key] = $clientServiceKey;
        }

        $container->getDefinition('sludio_helper.oauth.registry')->replaceArgument(1, $clientServiceKeys);
    }
}
