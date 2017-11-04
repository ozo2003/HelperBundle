<?php

namespace Sludio\HelperBundle\DependencyInjection\Component;

use Sludio\HelperBundle\DependencyInjection\Compiler\MiddlewarePass;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;
use Sludio\HelperBundle\Script\Utils\Helper;

class Guzzle implements ConfigureInterface
{
    protected $alias;

    public function configure(ContainerBuilder &$container, $alias)
    {
        $this->alias = $alias.'.guzzle';
        $dataCollector = $container->getDefinition($this->alias.'.data_collector.guzzle');
        $dataCollector->replaceArgument(0, $container->getParameter($this->alias.'.profiler')['max_body_size']);

        if (!$container->getParameter($this->alias.'.profiler')['enabled']) {
            $container->removeDefinition($this->alias.'.middleware.history');
            $container->removeDefinition($this->alias.'.middleware.stopwatch');
            $container->removeDefinition($this->alias.'.data_collector.guzzle');
        }

        $this->processLoggerConfiguration($container->getParameter($this->alias.'.logger'), $container);
        $this->processMockConfiguration($container->getParameter($this->alias.'.mock'), $container, $container->getParameter($this->alias.'.profiler')['enabled']);
        $this->processCacheConfiguration($container->getParameter($this->alias.'.cache'), $container, $container->getParameter($this->alias.'.profiler')['enabled']);
        $this->processClientsConfiguration($container->getParameter($this->alias.'.clients'), $container, $container->getParameter($this->alias.'.profiler')['enabled']);
    }

    private function processLoggerConfiguration(array $config, ContainerBuilder $container)
    {
        if (!$config['enabled']) {
            $container->removeDefinition($this->alias.'.middleware.logger');
            $container->removeDefinition($this->alias.'.logger.message_formatter');

            return;
        }

        $loggerDefinition = $container->getDefinition($this->alias.'.middleware.logger');

        if ($config['service']) {
            $loggerDefinition->replaceArgument(0, new Reference($config['service']));
        }

        if ($config['format']) {
            $formatterDefinition = $container->getDefinition($this->alias.'.logger.message_formatter');
            $formatterDefinition->replaceArgument(0, $config['format']);
        }

        if ($config['level']) {
            $loggerDefinition->replaceArgument(2, $config['level']);
        }
    }

    private function processMockConfiguration(array $config, ContainerBuilder $container, $debug)
    {
        if (!$config['enabled']) {
            $container->removeDefinition($this->alias.'.middleware.mock');
            $container->removeDefinition($this->alias.'.mock.storage');

            return;
        }

        $storage = $container->getDefinition($this->alias.'.mock.storage');
        $storage->setArguments([
            $config['storage_path'],
            $config['request_headers_blacklist'],
            $config['response_headers_blacklist'],
        ]);

        $middleware = $container->getDefinition($this->alias.'.middleware.mock');
        $middleware->replaceArgument(1, $config['mode']);
        $middleware->replaceArgument(2, $debug);
    }

    private function processCacheConfiguration(array $config, ContainerBuilder $container, $debug)
    {
        if (!$config['enabled'] || $config['disabled'] === true) {
            $container->removeDefinition($this->alias.'.middleware.cache');
            $container->removeDefinition($this->alias.'.cache_adapter.redis');

            return;
        }

        $container->getDefinition($this->alias.'.middleware.cache')->addArgument($debug);
        $container->getDefinition($this->alias.'.redis_cache')
            ->replaceArgument(0, new Reference('snc_redis.'.$container->getParameter('sludio_helper.redis.guzzle')));

        $container->setAlias($this->alias.'.cache_adapter', $config['adapter']);
    }

    private function processClientsConfiguration(array $config, ContainerBuilder $container, $debug)
    {
        foreach ($config as $name => $options) {
            $client = new Definition($options['class']);
            $client->setLazy($options['lazy']);
            $useAuthentication = $options['credentials']['enabled'];

            if ($useAuthentication === true) {
                if (!Helper::multiset(array_values($options['credentials']))) {
                    throw new InvalidArgumentException(sprintf('If authentication parameter is set, htpasswd user and password can not be null'));
                }
                $credentials = [
                    'auth' => [
                        $options['credentials']['user'],
                        $options['credentials']['pass'],
                        $options['authentication_type'],
                    ],
                ];

                if (!isset($options['config'])) {
                    $options['config'] = $credentials;
                } else {
                    $options['config'] = array_merge($options['config'], $credentials);
                }

            }

            if (isset($options['config'])) {
                if (!is_array($options['config'])) {
                    throw new InvalidArgumentException(sprintf('Config for "'.$this->alias.'.client.%s" should be an array, but got %s', $name, gettype($options['config'])));
                }
                $client->addArgument($this->buildGuzzleConfig($options['config'], $debug));
            }

            $attributes = [];

            if (!empty($options['middleware'])) {
                if ($debug) {
                    $addDebugMiddleware = true;

                    foreach ($options['middleware'] as $middleware) {
                        if ('!' === ($middleware[0])) {
                            $addDebugMiddleware = false;
                            break;
                        }
                    }

                    if ($addDebugMiddleware) {
                        $options['middleware'] = array_merge($options['middleware'], [
                            'stopwatch',
                            'history',
                            'logger',
                        ]);
                    }
                }

                $attributes['middleware'] = implode(' ', array_unique($options['middleware']));
            }

            $client->addTag(MiddlewarePass::CLIENT_TAG, $attributes);

            $clientServiceId = sprintf($this->alias.'.client.%s', $name);
            $container->setDefinition($clientServiceId, $client);

            if (isset($options['alias'])) {
                $container->setAlias($options['alias'], $clientServiceId);
            }
        }
    }

    private function buildGuzzleConfig(array $config, $debug)
    {
        if (isset($config['handler'])) {
            $config['handler'] = new Reference($config['handler']);
        }

        if ($debug && function_exists('curl_init')) {
            $config['on_stats'] = [
                new Reference($this->alias.'.data_collector.history_bag'),
                'addStats',
            ];
        }

        return $config;
    }
}
