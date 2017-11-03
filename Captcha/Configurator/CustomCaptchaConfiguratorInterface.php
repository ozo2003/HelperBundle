<?php

namespace Sludio\HelperBundle\Captcha\Configurator;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CustomCaptchaConfiguratorInterface implements CaptchaConfiguratorInterface
{

    public function buildConfiguration(NodeBuilder $node)
    {

    }

    public function configureClient(ContainerBuilder $container, $clientServiceKey, array $options = [])
    {

    }
}