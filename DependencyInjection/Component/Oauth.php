<?php

namespace Sludio\HelperBundle\DependencyInjection\Component;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use LogicException;

use Sludio\HelperBundle\Oauth\Configurator;

class Oauth implements Extensionable
{
    protected $checkExternalClassExistence;

    protected $configurators = [];

    protected $type;

    /**
     * List of available Oauth providers
     * @var array
     */
    protected static $supportedProviderTypes = [
        'custom' => Configurator\CustomProviderConfigurator::class,
        'facebook' => Configurator\FacebookProviderConfigurator::class,
        'google' => Configurator\GoogleProviderConfigurator::class,
        'twitter' => Configurator\TwitterProviderConfigurator::class,
        'draugiem' => Configurator\DraugiemProviderConfigurator::class,
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

    public function buildClientConfiguration(NodeDefinition &$node)
    {
        $optionsNode = $node->children();
        $optionsNode
            ->scalarNode('client_id')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('client_secret')->isRequired()->cannotBeEmpty()->end()
            ->booleanNode('use_state')->defaultValue(true)->end()
        ;

        $this->getConfigurator($this->getType())->buildConfiguration($optionsNode);
        $optionsNode->end();
    }

    public function configureClient(ContainerBuilder $container, $clientServiceKey, array $options = [])
    {
        $providerClass = $options['provider_class'];
        if ($this->checkExternalClassExistence && !class_exists($providerClass)) {
            throw new LogicException(sprintf(
                'Class "%s" does not exist.',
                $providerClass
            ));
        }

        $providerServiceKey = sprintf('sludio_helper.oauth.provider.%s', $clientServiceKey);

        $providerDefinition = $container->register(
            $providerServiceKey,
            $providerClass
        );
        $providerDefinition->setPublic(false);

        $providerDefinition->setFactory([
            new Reference('sludio_helper.oauth.provider_factory'),
            'createProvider',
        ]);

        $mandatory = [
            $providerClass,
            $options['provider_options']
        ];

        $optional = [];

        if (isset($options['provider_options']['params'])) {
            $optional[] = $options['provider_options']['params'];
        }

        $providerDefinition->setArguments(array_merge($mandatory, $optional));

        $clientServiceKey = sprintf('sludio_helper.oauth.client.%s', $clientServiceKey);
        $clientClass = $options['client_class'];
        $clientDefinition = $container->register(
            $clientServiceKey,
            $clientClass
        );
        $clientDefinition->setArguments([
            new Reference($providerServiceKey),
            new Reference('request_stack'),
            new Reference('sludio_helper.logger')
        ]);

        if (!$options['state']) {
            $clientDefinition->addMethodCall('setAsStateless');
        }

        return $clientServiceKey;
    }

    public function configure(ContainerBuilder &$container)
    {
        $clientConfigurations = $container->getParameter('sludio_helper.oauth.clients');
        $clientServiceKeys = [];
        foreach ($clientConfigurations as $key => $clientConfig) {
            $tree = new TreeBuilder();
            $processor = new Processor();

            if (!isset($clientConfig['type'])) {
                throw new InvalidConfigurationException(sprintf(
                    'Your "sludio_helper_oauth_client.clients.%s" config entry is missing the "type" key.',
                    $key
                ));
            }

            $this->type = $clientConfig['type'];
            unset($clientConfig['type']);
            if (!isset(self::$supportedProviderTypes[$this->type])) {
                $supportedKeys = array_keys(self::$supportedProviderTypes);
                sort($supportedKeys);
                throw new InvalidConfigurationException(sprintf(
                    'The "sludio_helper_oauth_client.clients" config "type" key "%s" is not supported. We support: %s',
                    $this->type,
                    implode(', ', $supportedKeys)
                ));
            }

            $node = $tree->root('sludio_helper_oauth_client/clients/'.$key);
            $this->buildClientConfiguration($node);
            $config = $processor->process($tree->buildTree(), [$clientConfig]);

            $configurator = $this->getConfigurator($this->type);

            $options = [
                'provider_class' => $configurator->getProviderClass($config),
                'client_class' => $configurator->getClientClass($config),
                'provider_options' => $configurator->getProviderOptions($config),
                'state' => $config['use_state']
            ];

            $clientServiceKey = $this->configureClient(
                $container,
                $key,
                $options
            );

            $service = [
                'key' => $clientServiceKey
            ];
            if (isset($config['provider_options']) && isset($config['provider_options']['name'])) {
                $service['name'] = $config['provider_options']['name'];
            } else {
                $service['name'] = ucfirst($key);
            }

            $clientServiceKeys[$key] = $service;
        }

        $container->getDefinition('sludio_helper.oauth.registry')->replaceArgument(1, $clientServiceKeys);
        $container->getDefinition('sludio_helper.registry')->replaceArgument(1, $clientServiceKeys);
    }

    /**
     * Get the value of Type
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the value of Type
     *
     * @param mixed type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }
}
