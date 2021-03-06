<?php

namespace Sludio\HelperBundle\DependencyInjection\Compiler;

use GuzzleHttp\HandlerStack;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Csa Guzzle middleware compiler pass.
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 * @author Tobias Schultze <http://tobion.de>
 */
class MiddlewarePass implements CompilerPassInterface
{
    const MIDDLEWARE_TAG = 'sludio_helper.guzzle.middleware';
    const CLIENT_TAG = 'sludio_helper.guzzle.client';

    public function process(ContainerBuilder $container)
    {
        $middleware = $this->findAvailableMiddleware($container);

        $this->registerMiddleware($container, $middleware);
    }

    /**
     * Fetches the list of available middleware.
     *
     * @param ContainerBuilder $container
     *
     * @return array
     * @throws LogicException
     */
    private function findAvailableMiddleware(ContainerBuilder $container)
    {
        $services = $container->findTaggedServiceIds(self::MIDDLEWARE_TAG);
        $middleware = [];

        foreach ($services as $id => $tags) {
            if (\count($tags) > 1) {
                throw new LogicException(sprintf('Middleware should only use a single \'%s\' tag', self::MIDDLEWARE_TAG));
            }

            if (!isset($tags[0]['alias'])) {
                throw new LogicException(sprintf('The \'alias\' attribute is mandatory for the \'%s\' tag', self::MIDDLEWARE_TAG));
            }

            $priority = isset($tags[0]['priority']) ? $tags[0]['priority'] : 0;

            $middleware[$priority][] = [
                'alias' => $tags[0]['alias'],
                'id' => $id,
            ];
        }

        krsort($middleware);

        return !empty($middleware) ? \call_user_func_array('array_merge', $middleware) : [];
    }

    private function getHandlerStack($options, $container, &$handlerStack)
    {
        if (!isset($options['handler'])) {
            $handlerStack = new Definition(HandlerStack::class);
            $handlerStack->setFactory([
                HandlerStack::class,
                'create',
            ]);
            $handlerStack->setPublic(false);
        } else {
            $handlerStack = $this->wrapHandlerInHandlerStack($options['handler'], $container);
        }
    }

    private function makeOptions(array $arguments = [], &$options)
    {
        $options = [];
        if (!empty($arguments)) {
            $options = array_shift($arguments);
        }
    }

    /**
     * Sets up handlers and registers middleware for each tagged client.
     *
     * @param ContainerBuilder $container
     * @param array            $middlewareBag
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     */
    private function registerMiddleware(ContainerBuilder $container, array $middlewareBag)
    {
        $clients = $container->findTaggedServiceIds(self::CLIENT_TAG);

        foreach ($clients as $clientId => $tags) {
            if (\count($tags) > 1) {
                throw new LogicException(sprintf('Clients should use a single \'%s\' tag', self::CLIENT_TAG));
            }

            try {
                $clientMiddleware = $this->filterClientMiddleware($middlewareBag, $tags);
            } catch (LogicException $e) {
                continue;
            }

            if (empty($clientMiddleware)) {
                continue;
            }

            $clientDefinition = $container->findDefinition($clientId);

            $arguments = $clientDefinition->getArguments();
            $this->makeOptions($arguments, $options);

            $this->getHandlerStack($options, $container, $handlerStack);
            $this->addMiddlewareToHandlerStack($handlerStack, $clientMiddleware);
            $options['handler'] = $handlerStack;

            array_unshift($arguments, $options);
            $clientDefinition->setArguments($arguments);
        }
    }

    private function makeLists($tag, &$whiteList, &$blackList)
    {
        $whiteList = $blackList = [];
        $clientMiddlewareList = explode(' ', $tag['middleware']);
        foreach ($clientMiddlewareList as $middleware) {
            if ('!' === $middleware[0]) {
                $blackList[] = substr($middleware, 1);
            } else {
                $whiteList[] = $middleware;
            }
        }

        if ($whiteList && $blackList) {
            throw new LogicException('You cannot mix whitelisting and blacklisting of middleware at the same time.');
        }
    }

    /**
     * @param array $middlewareBag The list of availables middleware
     * @param array $tags          The tags containing middleware configuration
     *
     * @return array The list of middleware to enable for the client
     *
     * @throws LogicException When middleware configuration is invalid
     */
    private function filterClientMiddleware(array $middlewareBag, array $tags)
    {
        if (!isset($tags[0]['middleware'])) {
            return !empty($middlewareBag) ? $middlewareBag : null;
        }

        $this->makeLists($tags[0], $whiteList, $blackList);

        if ($whiteList) {
            return array_filter($middlewareBag, function ($value) use ($whiteList) {
                return \in_array($value['alias'], $whiteList, true);
            });
        }

        return array_filter($middlewareBag, function ($value) use ($blackList) {
            return !\in_array($value['alias'], $blackList, true);
        });
    }

    /**
     * @param Reference|Definition|callable $handler   The configured Guzzle handler
     * @param ContainerBuilder              $container The container builder
     *
     * @return Definition
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     */
    private function wrapHandlerInHandlerStack($handler, ContainerBuilder $container)
    {
        if ($handler instanceof Reference) {
            $handler = $container->getDefinition((string)$handler);
        }

        if ($handler instanceof Definition && HandlerStack::class === $handler->getClass()) {
            // no need to wrap the Guzzle handler if it already resolves to a HandlerStack
            return $handler;
        }

        $handlerDefinition = new Definition(HandlerStack::class);
        $handlerDefinition->setArguments([$handler]);
        $handlerDefinition->setPublic(false);

        return $handlerDefinition;
    }

    private function addMiddlewareToHandlerStack(Definition $handlerStack, array $middlewareBag)
    {
        foreach ($middlewareBag as $middleware) {
            $handlerStack->addMethodCall('push', [
                new Reference($middleware['id']),
                $middleware['alias'],
            ]);
        }
    }
}
