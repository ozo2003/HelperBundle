<?php

namespace Sludio\HelperBundle\DependencyInjection\Requirement;

use Abraham\TwitterOAuth\TwitterOAuth;
use League\OAuth1\Client\Server\Twitter;
use League\OAuth2\Client\Provider\AbstractProvider;
use Psr\Http\Message\ResponseInterface;

class Oauth extends AbstractRequirement
{
    /**
     * @var array
     */
    protected static $requirements = [
        TwitterOAuth::class => 'abraham/twitteroauth',
        ResponseInterface::class => 'psr/http-message',
        AbstractProvider::class => 'league/oauth2-client',
        Twitter::class => 'league/oauth1-client',
    ];

    public function getRequirements()
    {
        return self::$requirements;
    }
}
