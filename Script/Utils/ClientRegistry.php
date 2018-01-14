<?php

namespace Sludio\HelperBundle\Script\Utils;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ClientRegistry
{
    private $container;

    private $serviceMap;

    public function __construct(ContainerInterface $container, array $serviceMap = [], $_ = null)
    {
        $this->container = $container;
        $arguments = \func_get_args();
        unset($arguments[0]);

        $used = [];
        foreach ($arguments as $argument) {
            if (!\is_array($argument)) {
                continue;
            }
            $checkExists = \array_intersect($used, array_keys($argument));
            $count = \count($checkExists);
            if ($count !== 0) {
                throw new InvalidArgumentException(sprintf('Multiple clients with same key is not allowed! Key'.($count > 1 ? 's' : '').' "%s" appear in configuration more than once!', implode(',', $checkExists)));
            }
            $used = array_merge($used, array_keys($argument));
        }

        $this->serviceMap = $used;
    }

    public function getClient($key)
    {
        if (!$this->hasClient($key)) {
            throw new InvalidArgumentException('error_client_not_found');
        }

        return $this->container->get($this->serviceMap[$key]['key']);
    }

    public function hasClient($key)
    {
        return isset($this->serviceMap[$key]);
    }

    public function getNameByClient($key = '')
    {
        if ($key !== '' && isset($this->serviceMap[$key])) {
            return $this->serviceMap[$key]['name'];
        }

        return $key;
    }

    public function getClients()
    {
        return $this->serviceMap;
    }
}
