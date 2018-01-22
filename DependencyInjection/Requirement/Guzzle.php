<?php

namespace Sludio\HelperBundle\DependencyInjection\Requirement;

use GuzzleHttp\ClientInterface;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Stopwatch\Stopwatch;

class Guzzle extends AbstractRequirement
{
    /**
     * @var array
     */
    protected $requirements = [
        ClientInterface::class => 'guzzlehttp/guzzle:~6.0',
        CacheItemInterface::class => 'psr/cache:^1.0',
        Stopwatch::class => 'symfony/stopwatch',
        Filesystem::class => 'symfony/filesystem',
    ];

    public function getRequirements()
    {
        return $this->requirements;
    }
}
