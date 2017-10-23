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
        $resources = $container->getParameter('twig.form.resources');

        $forms = [
            'sludio_helper.translatable.enabled' => 'sludio_helper.translatable.template',
            'sludio_helper.captcha.enabled' => 'sludio_helper.captcha.client.recaptcha.template'
        ];

        foreach ($forms as $check => $form) {
            if ($container->hasParameter($check) && $container->getParameter($check) == true) {
                if ($container->hasParameter($form)) {
                    if (false !== ($template = $container->getParameter($form))) {
                        if (!in_array($template, $resources)) {
                            $resources[] = $template;
                        }
                    }
                }
            }
        }

        $container->setParameter('twig.form.resources', $resources);
    }
}