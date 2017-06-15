<?php

namespace Sludio\HelperBundle\DependencyInjection;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ProviderFactory
{
    private $generator;
    private $request;

    public function __construct(UrlGeneratorInterface $generator, RequestStack $requestStack)
    {
        $this->generator = $generator;
        $this->request = $requestStack->getCurrentRequest();
    }

    public function createProvider($class, array $options, $redirectUri = null, array $redirectParams = [])
    {
        $redirectUri = $this->generateUrl($redirectUri, $redirectParams);

        $options['redirectUri'] = $redirectUri;
        $collaborators = [];

        return new $class($options, $collaborators, $this->generator);
    }

    public function generateUrl($redirectUri = null, array $redirectParams = [])
    {
        $this->getUrlToken($redirectParams);
        $redirectUri = $this->generator
            ->generate($redirectUri, $redirectParams, UrlGeneratorInterface::ABSOLUTE_URL);

        return $redirectUri;
    }

    private function getUrlToken(array &$redirectParams = [])
    {
        if (array_key_exists('token', $redirectParams) && $redirectParams['token'] === null) {
            $redirectParams['token'] = base64_encode(http_build_query($this->request->query->all()));
        }
    }
}
