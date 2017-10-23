<?php

/**
 * This file is part of the SludioHelperBundle package.
 *
 * @author Dāvis Zālītis <davis@source.lv>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sludio\HelperBundle;

use Sludio\HelperBundle\DependencyInjection\Compiler;
use Sludio\HelperBundle\DependencyInjection\SludioHelperExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SludioHelperBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new Compiler\TemplatingPass());
        $container->addCompilerPass(new Compiler\LoaderPass());
        $container->addCompilerPass(new Compiler\MiddlewarePass());
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        if ($this->extension === null) {
            return new SludioHelperExtension();
        }

        return $this->extension;
    }
}
