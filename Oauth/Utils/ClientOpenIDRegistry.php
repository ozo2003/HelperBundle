<?php

namespace Sludio\HelperBundle\Oauth\Utils;

use Exception;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ClientOpenIDRegistry
{
    private $container;

    private $serviceMap;

    public function __construct(ContainerBuilder $container, array $oAuthServiceMap, array $openIDServiceMap)
    {
        $this->container = $container;

        $keysOauth = array_keys($oAuthServiceMap);
        $keysOpenid = array_keys($openIDServiceMap);

        $checkExists = array_intersect($keysOauth, $keysOpenid);
        if (!empty($checkExists)) {
            throw new Exception(sprintf('Multiple clients with same key is not allowed! Key'.(count($checkExists) > 1 ? 's' : '').' "%s" appear in configuration more than once!', implode(',', $checkExists)));
        }

        $this->serviceMap = $openIDServiceMap + $oAuthServiceMap;
    }

    public function getClient($key)
    {
        if (!$this->hasClient($key)) {
            throw new InvalidArgumentException('error_oauth_client_not_found');
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
