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
            throw new \InvalidArgumentException('error_oauth_client_not_found');
        }

        return $this->container->get($this->serviceMap[$key]['key']);
    }

    public function hasClient($key){
        return isset($this->serviceMap[$key]);
    }

    public function getNameByClient($key = null)
    {
        if ($key && isset($this->serviceMap[$key])) {
            return $this->serviceMap[$key]['name'];
        }

        return '';
    }

    public function getClients()
    {
        return $this->serviceMap;
    }
}
