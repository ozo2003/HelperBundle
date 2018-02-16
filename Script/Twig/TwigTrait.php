<?php

namespace Sludio\HelperBundle\Script\Twig;

trait TwigTrait
{
    private $shortFunctions;

    public function makeArray(array $input, $type = 'filter')
    {
        $output = [];
        $class = '\\Twig_Simple'.ucfirst($type);
        $this->makeInput($input, $input);

        foreach ($input as $call => $function) {
            if (\is_array($function)) {
                $options = [];
                if (isset($function[2])) {
                    $options[] = $function[2];
                    unset($function[2]);
                }
                $output[] = new $class($call, $function, $options);
            } else {
                $output[] = new $class($call, [
                    $this,
                    $function,
                ]);
            }
        }

        return $output;
    }

    private function makeInput(array $input, &$output)
    {
        $output = [];
        foreach ($input as $call => $function) {
            if ($this->shortFunctions) {
                $output[$call] = $function;
            }
            $output['sludio_'.$call] = $function;
        }
    }
}
