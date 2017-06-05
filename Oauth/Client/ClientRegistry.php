<?php

namespace Sludio\HelperBundle\Oauth\Client;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ClientRegistry
{
    private $container;

    private $serviceMap;

    public function __construct(ContainerInterface $container, array $serviceMap)
    {
        $this->container = $container;
        $this->serviceMap = $serviceMap;
    }

    public function getClient($key)
    {
        if (!$this->hasClient($key)) {
            throw new \InvalidArgumentException(sprintf(
                'There is no OAuth2 client called "%s". Available are: %s',
                $key,
                implode(', ', array_keys($this->serviceMap))
            ));
        }

        return $this->container->get($this->serviceMap[$key]);
    }

    public function hasClient($key){
        return isset($this->serviceMap[$key]);
    }
}
