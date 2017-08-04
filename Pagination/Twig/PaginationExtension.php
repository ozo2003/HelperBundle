<?php

namespace Sludio\HelperBundle\Pagination\Twig;

use Sludio\HelperBundle\Pagination\Twig\Behaviour\PaginationBehaviour;
use Sludio\HelperBundle\Pagination\Twig\Behaviour\FixedLength;
use Twig_SimpleFunction;
use Twig_Extension;

class PaginationExtension extends Twig_Extension
{
    /**
     * @var PaginationBehaviour[]
     */
    private $functions;

    public function __construct($container)
    {
        $short_functions = $container->hasParameter('sludio_helper.script.short_functions') && $container->getParameter('sludio_helper.script.short_functions', false);

        $this->functions = [];
        if ($container->hasParameter('sludio_helper.pagination.behaviour') && !empty($container->getParameter('sludio_helper.pagination.behaviour', []))) {
            $functions = $container->getParameter('sludio_helper.pagination.behaviour');
            foreach ($functions as $function) {
                array_push($this->functions, $this->withFunction(array_keys($function)[0], array_values($function)[0]));
            }
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sludio_helper.twig.pagination_extension';
    }

    public function withFunction($functionName, $behaviour)
    {
        $functionName = $this->suffixFunctionName($functionName);
        $behaviour = new FixedLength($behaviour);

        $c = clone $this;

        $c->functions[$functionName] = new Twig_SimpleFunction(
            $functionName,
            array($behaviour, 'getPaginationData')
        );

        return $c->functions[$functionName];
    }

    public function withoutFunction($functionName)
    {
        $functionName = $this->suffixFunctionName($functionName);

        $c = clone $this;
        unset($c->functions[$functionName]);

        return $c;
    }

    /**
     * @param string $functionName
     *
     * @return string
     */
    private function suffixFunctionName($functionName)
    {
        // Make sure the function name is not suffixed twice.
        $functionName = preg_replace('/(_pagination)$/', '', (string) $functionName);

        return $functionName.'_pagination';
    }

    /**
     * @return \Twig_SimpleFunction[]
     */
    public function getFunctions()
    {
        return array_values($this->functions);
    }
}
