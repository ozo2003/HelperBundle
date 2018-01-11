<?php

namespace Sludio\HelperBundle\DependencyInjection\Requirement;

use GuzzleHttp\ClientInterface;
use Psr\Cache\CacheItemInterface;
use Namshi\Cuzzle\Formatter\CurlFormatter;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Filesystem\Filesystem;

class Guzzle extends AbstractRequirement
{
    protected $requirements = [
        ClientInterface::class => 'guzzlehttp/guzzle~6.0',
        CacheItemInterface::class => 'psr/cache^1.0',
        CurlFormatter::class => 'namshi/cuzzle^2.0',
        Stopwatch::class => 'symfony/stopwatch',
        Filesystem::class => 'symfony/filesystem'
    ];

    public function getRequirements()
    {
        return $this->requirements;
    }
}
