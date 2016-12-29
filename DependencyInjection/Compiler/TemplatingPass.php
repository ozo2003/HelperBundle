<?php

namespace Sludio\HelperBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TemplatingPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasParameter('sludio_helper.translatable.template')) {
            if (false !== ($template = $container->getParameter('sludio_helper.translatable.template'))) {
                $resources = $container->getParameter('twig.form.resources');

                if (!in_array($template, $resources)) {
                    $resources[] = $template;
                    $container->setParameter('twig.form.resources', $resources);
                }
            }
        }
    }
}
