<?php

namespace Sludio\HelperBundle\Pagination\Twig;

use Sludio\HelperBundle\Pagination\Twig\Behaviour\PaginationBehaviourInterface;
use Sludio\HelperBundle\Pagination\Twig\Behaviour\FixedLength;

class PaginationExtension extends \Twig_Extension
{
    /**
     * @var PaginationBehaviourInterface[]
     */
    private $functions;

    public function __construct()
    {
        $this->functions = [
            $this->withFunction('sludio_small', 7),
        ];
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

        $c/*->functions[$functionName]*/ = new \Twig_SimpleFunction(
            $functionName,
            array($behaviour, 'getPaginationData')
        );

        return $c;
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
