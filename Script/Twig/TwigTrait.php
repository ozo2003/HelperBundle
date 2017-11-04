<?php

namespace Sludio\HelperBundle\Script\Twig;

trait TwigTrait
{
    protected $shortFunctions;

    private function makeInput($input, &$output)
    {
        $output = [];
        foreach ($input as $call => $function) {
            if ($this->shortFunctions) {
                $output[$call] = $function;
            }
            $output['sludio_'.$call] = $function;
        }
    }

    public function makeArray(array $input, $type = 'filter')
    {
        $output = [];
        $class = '\\Twig_Simple'.ucfirst($type);
        $this->makeInput($input, $input);

        foreach ($input as $call => $function) {
            if (is_array($function)) {
                $output[] = new $class($call, $function);
            } else {
                $output[] = new $class($call, [
                    $this,
                    $function,
                ]);
            }
        }

        return $output;
    }
}
