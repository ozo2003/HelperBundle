<?php

namespace Sludio\HelperBundle\Oauth\Client;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ClientOpenIDRegistry
{
    private $container;

    private $serviceMap;

    public function __construct(ContainerInterface $container, array $oAuthServiceMap, array $openIDServiceMap)
    {
        $this->container = $container;
        $this->serviceMap = $oAuthServiceMap + $openIDServiceMap;
    }

    public function getClient($key)
    {
        if (!$this->hasClient($key)) {
            throw new \InvalidArgumentException('error_oauth_client_not_found');
        }

        return $this->container->get($this->serviceMap[$key]['key']);
    }

    public function hasClient($key)
    {
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
