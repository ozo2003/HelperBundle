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

class Guzzle
{
    const NAME = 'sludio_helper.guzzle';

    public function configure(ContainerBuilder &$container)
    {
        $dataCollector = $container->getDefinition(self::NAME.'.data_collector.guzzle');
        $dataCollector->replaceArgument(0, $container->getParameter(self::NAME.'.profiler')['max_body_size']);

        if (!$container->getParameter(self::NAME.'.profiler')['enabled']) {
            $container->removeDefinition(self::NAME.'.middleware.history');
            $container->removeDefinition(self::NAME.'.middleware.stopwatch');
            $container->removeDefinition(self::NAME.'.data_collector.guzzle');
        }

        $this->processLoggerConfiguration($container->getParameter(self::NAME.'.logger'), $container);
        $this->processMockConfiguration($container->getParameter(self::NAME.'.mock'), $container, $container->getParameter(self::NAME.'.profiler')['enabled']);
        $this->processCacheConfiguration($container->getParameter(self::NAME.'.cache'), $container, $container->getParameter(self::NAME.'.profiler')['enabled']);
        $this->processClientsConfiguration($container->getParameter(self::NAME.'.clients'), $container, $container->getParameter(self::NAME.'.profiler')['enabled']);
    }

    private function processLoggerConfiguration(array $config, ContainerBuilder $container)
    {
        if (!$config['enabled']) {
            $container->removeDefinition(self::NAME.'.middleware.logger');
            $container->removeDefinition(self::NAME.'.logger.message_formatter');

            return;
        }

        $loggerDefinition = $container->getDefinition(self::NAME.'.middleware.logger');

        if ($config['service']) {
            $loggerDefinition->replaceArgument(0, new Reference($config['service']));
        }

        if ($config['format']) {
            $formatterDefinition = $container->getDefinition(self::NAME.'.logger.message_formatter');
            $formatterDefinition->replaceArgument(0, $config['format']);
        }

        if ($config['level']) {
            $loggerDefinition->replaceArgument(2, $config['level']);
        }
    }

    private function processMockConfiguration(array $config, ContainerBuilder $container, $debug)
    {
        if (!$config['enabled']) {
            $container->removeDefinition(self::NAME.'.middleware.mock');
            $container->removeDefinition(self::NAME.'.mock.storage');

            return;
        }

        $storage = $container->getDefinition(self::NAME.'.mock.storage');
        $storage->setArguments([
            $config['storage_path'],
            $config['request_headers_blacklist'],
            $config['response_headers_blacklist'],
        ]);

        $middleware = $container->getDefinition(self::NAME.'.middleware.mock');
        $middleware->replaceArgument(1, $config['mode']);
        $middleware->replaceArgument(2, $debug);
    }

    private function processCacheConfiguration(array $config, ContainerBuilder $container, $debug)
    {
        if (!$config['enabled']) {
            $container->removeDefinition(self::NAME.'.middleware.cache');
            $container->removeDefinition(self::NAME.'.cache_adapter.redis');

            return;
        }

        $container->getDefinition(self::NAME.'.middleware.cache')->addArgument($debug);
        $container->getDefinition(self::NAME.'.redis_cache')
            ->replaceArgument(0, new Reference('snc_redis.'.$container->getParameter('sludio_helper.redis.guzzle')));

        $container->setAlias(self::NAME.'.cache_adapter', $config['adapter']);
    }

    private function processClientsConfiguration(array $config, ContainerBuilder $container, $debug)
    {
        foreach ($config as $name => $options) {
            $client = new Definition($options['class']);
            $client->setLazy($options['lazy']);

            if (isset($options['config'])) {
                if (!is_array($options['config'])) {
                    throw new InvalidArgumentException(sprintf('Config for "'.self::NAME.'.client.%s" should be an array, but got %s', $name, gettype($options['config'])));
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

            $clientServiceId = sprintf(self::NAME.'.client.%s', $name);
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
                new Reference(self::NAME.'.data_collector.history_bag'),
                'addStats',
            ];
        }

        return $config;
    }
}