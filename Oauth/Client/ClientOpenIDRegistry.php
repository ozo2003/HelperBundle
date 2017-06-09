<?php

namespace Sludio\HelperBundle\Oauth\Client;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ClientOpenIDRegistry {
    private $container;

    private $serviceMap;

    public function __construct(ContainerInterface $container, array $oAuthServiceMap, array $openIDServiceMap)
    {
        $this->container = $container;
        $this->serviceMap = array_merge($oAuthServiceMap, $openIDServiceMap);
    }

    public function getClient($key)
    {
        if (!$this->hasClient($key)) {
            throw new \InvalidArgumentException(sprintf(
                'There is no OAuth2 and no OpenID client called "%s". Available are: %s',
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
