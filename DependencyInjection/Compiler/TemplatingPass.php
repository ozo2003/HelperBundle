<?php

/**
 * This file is part of the SludioHelperBundle package.
 *
 * @author Dāvis Zālītis <davis@source.lv>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
        if ($container->hasParameter('sludio_helper.translatable.template_new')) {
            if (false !== ($template = $container->getParameter('sludio_helper.translatable.template_new'))) {
                $resources = $container->getParameter('twig.form.resources');

                if (!in_array($template, $resources)) {
                    $resources[] = $template;
                    $container->setParameter('twig.form.resources', $resources);
                }
            }
        }
    }
}
