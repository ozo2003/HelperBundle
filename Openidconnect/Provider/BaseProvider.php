<?php

namespace Sludio\HelperBundle\Openidconnect\Provider;

class BaseProvider extends OpenIDConnectProvider
{
    public function getValidateTokenUrl()
    {
        return '';
    }

    public function getRefreshTokenUrl()
    {
        return '';
    }

    public function getRevokeTokenUrl()
    {
        return '';
    }
}
