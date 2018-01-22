<?php

namespace Sludio\HelperBundle\DependencyInjection\Requirement;

use Lcobucci\JWT\Token;
use League\OAuth2\Client\Provider\AbstractProvider;
use Psr\Http\Message\ResponseInterface;

class Openidconnect extends AbstractRequirement
{
    /**
     * @var array
     */
    protected $requirements = [
        Token::class => 'lcobucci/jwt',
        AbstractProvider::class => 'league/oauth2-client',
        ResponseInterface::class => 'psr/http-message',
    ];

    public function getRequirements()
    {
        return $this->requirements;
    }
}
