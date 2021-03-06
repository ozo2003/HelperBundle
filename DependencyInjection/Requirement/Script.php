<?php

namespace Sludio\HelperBundle\DependencyInjection\Requirement;

use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class Script extends AbstractRequirement
{
    /**
     * @var array
     */
    protected static $requirements = [
        ResponseInterface::class => 'psr/http-message',
        LoggerInterface::class => 'psr/log',
    ];

    public function getRequirements()
    {
        return self::$requirements;
    }
}
