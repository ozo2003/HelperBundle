<?php

namespace Sludio\HelperBundle\Script\Twig;

trait TwigTrait
{
    protected $shortFunctions;

    public function makeArray(array $input, $type = 'filter', $onlyShort = false)
    {
        if($onlyShort){
            $this->shortFunctions = true;
        }
        $output = [];
        $class = '\\Twig_Simple'.ucfirst($type);

        foreach ($input as $call => $function) {
            $output[] = new $class('sludio_'.$call, [
                $this,
                $function,
            ]);
            if ($this->shortFunctions) {
                $output[] = new $class($call, [
                    $this,
                    $function,
                ]);
            }
        }

        return $output;
    }
}