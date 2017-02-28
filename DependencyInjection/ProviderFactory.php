<?php

namespace Sludio\HelperBundle\DependencyInjection;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProviderFactory
{
    private $generator;

    public function __construct(UrlGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    public function createProvider($class, array $options, $redirectUri, array $redirectParams = [])
    {
        $redirectUri = $this->generator
            ->generate($redirectUri, $redirectParams, UrlGeneratorInterface::ABSOLUTE_URL);

        $options['redirectUri'] = $redirectUri;
        $collaborators = [];

        return new $class($options, $collaborators);
    }
}
