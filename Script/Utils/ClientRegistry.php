<?php

namespace Sludio\HelperBundle\Script\Utils;

use Sludio\HelperBundle\Script\Security\Exception\ErrorException;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ClientRegistry
{
    /** @var ContainerInterface */
    private $container;

    private $serviceMap;

    /**
     * ClientRegistry constructor.
     *
     * @param PsrContainerInterface $interface
     *
     * @throws ErrorException
     */
    public function __construct(PsrContainerInterface $interface)
    {
        if (!$interface instanceof ContainerInterface) {
            throw new ErrorException(sprintf('Wrong container instance class: %s, %s excpected', \get_class($interface), ContainerInterface::class));
        }

        $this->container = $interface;
        $arguments = \func_get_args();
        unset($arguments[0]);

        $used = [];
        foreach ($arguments as $argument) {
            if (!\is_array($argument)) {
                continue;
            }
            $checkExists = \array_intersect(array_keys($used), array_keys($argument));
            $count = \count($checkExists);
            if ($count !== 0) {
                throw new ErrorException(sprintf('Multiple clients with same key is not allowed! Key'.($count > 1 ? 's' : '').' "%s" appear in configuration more than once!', implode(',', $checkExists)));
            }
            $used = array_merge($used, $argument);
        }

        $this->serviceMap = $used;
    }

    /**
     * @param $key
     *
     * @throws ErrorException
     *
     * @return mixed
     */
    public function getClient($key)
    {
        if (!$this->hasClient($key)) {
            throw new ErrorException(sprintf('Client "%s" not found in registry', $key));
        }

        return $this->container->get($this->serviceMap[$key]['key']);
    }

    public function hasClient($key)
    {
        return isset($this->serviceMap[$key]);
    }

    public function getNameByClient($key = '')
    {
        if ($key !== '' && $this->hasClient($key)) {
            return $this->serviceMap[$key]['name'];
        }

        return $key;
    }

    public function getClients()
    {
        return $this->serviceMap;
    }
}
