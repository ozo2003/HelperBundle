<?php

namespace Sludio\HelperBundle\Pagination\Twig;

use Sludio\HelperBundle\Pagination\Twig\Behaviour\FixedLength;
use Sludio\HelperBundle\Pagination\Twig\Behaviour\PaginationBehaviour;

class PaginationExtension extends \Twig_Extension
{
    /**
     * @var PaginationBehaviour[]
     */
    private $functions;

    public function __construct($container, $shortFunctions)
    {
        $this->functions = [];
        if ($container->hasParameter('sludio_helper.pagination.behaviour') && !empty($container->getParameter('sludio_helper.pagination.behaviour', []))) {
            $functions = $container->getParameter('sludio_helper.pagination.behaviour');
            foreach ($functions as $function) {
                if ($shortFunctions) {
                    array_push($this->functions, $this->withFunction(array_keys($function)[0], array_values($function)[0]));
                }
                array_push($this->functions, $this->withFunction('sludio_'.array_keys($function)[0], array_values($function)[0]));
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

        $clone = clone $this;

        $clone->functions[$functionName] = new \Twig_SimpleFunction($functionName, [
            $behaviour,
            'getPaginationData',
        ]);

        return $clone->functions[$functionName];
    }

    public function withoutFunction($functionName)
    {
        $functionName = $this->suffixFunctionName($functionName);

        $clone = clone $this;
        unset($clone->functions[$functionName]);

        return $clone;
    }

    /**
     * @param string $functionName
     *
     * @return string
     */
    private function suffixFunctionName($functionName)
    {
        // Make sure the function name is not suffixed twice.
        $functionName = preg_replace('/(_pagination)$/', '', (string)$functionName);

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
