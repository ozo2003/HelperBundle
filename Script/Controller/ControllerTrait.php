<?php

namespace Sludio\HelperBundle\Script\Controller;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Console\Output\BufferedOutput;

trait ControllerTrait
{
    protected $container;

    private function result($success = 1, $code = 200)
    {
        return new JsonResponse(['success' => intval($success)], intval($code), [
            'Cache-Control' => 'no-cache',
        ]);
    }

    private function resultXml($data, $code = 200)
    {
        return new Response($data, intval($code), [
            'Content-Type' => 'application/xml',
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
        $output = new BufferedOutput();
        $application->run($input);

        if ($return === true) {
            return $this->result();
        }

        $content = $output->fetch();

        return $content;
    }
}