<?php

namespace Sludio\HelperBundle\Script\Controller;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpFoundation\JsonResponse;

trait ControllerTrait
{
    public $container;

    private function result()
    {
        return new JsonResponse(['success' => 1], 200, [
            'Cache-Control' => 'no-cache',
        ]);
    }

    private function runApp($command, $params = [], $return = true)
    {
        $data = [
            'command' => $command,
        ];
        if (!empty($params)) {
            $data = array_merge($data, $params);
        }

        $application = new Application($this->container->get('kernel'));
        $application->setAutoExit(false);
        $input = new ArrayInput($data);
        $application->run($input);

        if ($return === true) {
            return $this->result();
        }
    }
}