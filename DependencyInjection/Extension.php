<?php

namespace Sludio\HelperBundle\DependencyInjection;

use Sludio\HelperBundle\DependencyInjection\Extension;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class Extension
{
    protected $ext;

    public function __construct($type)
    {
        switch ($type) {
            case 'oauth':
                $this->ext = new Extension\OAuth();
                break;
            case 'openid':
                $this->ext = new Extension\OpenID();
                break;
            case 'openidconnect':
                $this->ext = new Extension\OpenIDConnect();
                break;
            case 'captcha':
                $this->ext = new Extension\Captcha();
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
