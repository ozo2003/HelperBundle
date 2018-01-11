<?php

namespace Sludio\HelperBundle\DependencyInjection\Requirement;

use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Mobile_Detect;

class Script extends AbstractRequirement
{
    protected $requirements = [
        ResponseInterface::class => 'psr/http-message',
        LoggerInterface::class => 'psr/log',
        Mobile_Detect::class => 'mobiledetect/mobiledetectlib'
    ];

    public function getRequirements()
    {
        return $this->requirements;
    }
}
