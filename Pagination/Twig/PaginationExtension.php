<?php

namespace Sludio\HelperBundle\Pagination\Twig;

use Sludio\HelperBundle\Pagination\Twig\Behaviour\BehaviourInterface;
use Sludio\HelperBundle\Pagination\Twig\Behaviour\FixedLength;

class PaginationExtension extends \Twig_Extension
{
    /**
     * @var BehaviourInterface[]
     */
    private $functions;

    public function __construct($container, $shortFunctions)
    {
        $this->functions = [];
        if ($container->hasParameter('sludio_helper.pagination.behaviour') && !empty($container->getParameter('sludio_helper.pagination.behaviour', []))) {
            $functions = $container->getParameter('sludio_helper.pagination.behaviour');
            /** @var $functions array */
            foreach ($functions as $function) {
                if ($shortFunctions) {
                    $this->functions[] = $this->withFunction(array_keys($function)[0], array_values($function)[0]);
                }
                $this->functions[] = $this->withFunction('sludio_'.array_keys($function)[0], array_values($function)[0]);
            }
        }
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

    /**
     * @param string $functionName
     *
     * @return string
     */
    private function suffixFunctionName($functionName)
    {
        // Make sure the function name is not suffixed twice.
        $functionName = (string)preg_replace('/(_pagination)$/', '', (string)$functionName);

        return $functionName.'_pagination';
    }

    public function withoutFunction($functionName)
    {
        $functionName = $this->suffixFunctionName($functionName);

        $clone = clone $this;
        unset($clone->functions[$functionName]);

        return $clone;
    }

    /**
     * @return \Twig_SimpleFunction[]
     */
    public function getFunctions()
    {
        return array_values($this->functions);
    }
}
