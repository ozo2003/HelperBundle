<?php

namespace Sludio\HelperBundle\DependencyInjection;

use Sludio\HelperBundle\DependencyInjection\Extension\OAuth;
use Sludio\HelperBundle\DependencyInjection\Extension\OpenID;
use Sludio\HelperBundle\DependencyInjection\Extension\OpenIDConnect;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class Extension
{
    protected $ext;

    public function __construct(string $type)
    {
        switch ($type) {
            case 'oauth':
                $this->ext = new OAuth();
                break;
            case 'openid':
                $this->ext = new OpenID();
                break;
            case 'openidconnect':
                $this->ext = new OpenIDConnect();
                break;
        }
    }

    public function configure(ContainerBuilder &$container)
    {
        return $this->ext->configure($container);
    }

    public function getExtension()
    {
        return $this->ext;
    }
}
