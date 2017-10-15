<?php

namespace Sludio\HelperBundle\Script\Controller;

use Predis\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpFoundation\JsonResponse;

class ScriptController extends Controller
{
    public function redisAction()
    {
        $data['success'] = 1;

        $clients = [];
        foreach ($this->container->getServiceIds() as $id) {
            if (substr($id, 0, 9) === 'snc_redis' && $this->container->get($id) instanceof Client) {
                $clients[] = $id;
            }
        }

        foreach ($clients as $snc) {
            $this->container->get($snc)->flushdb();
        }

        return new JsonResponse($data, 200, [
            'Cache-Control' => 'no-cache',
        ]);
    }

    public function cacheAction()
    {
        global $kernel;

        if ('AppCache' === get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }

        $commands = [
            'clear',
            'warmup',
        ];
        foreach ($commands as $command) {
            $application = new Application($kernel);
            $application->setAutoExit(false);
            $input = new ArrayInput([
                'command' => 'cache:'.$command,
                '--env' => $kernel->getEnvironment(),
            ]);
            $application->run($input);
            $data['success'] = 1;
        }

        return new JsonResponse($data, 200, [
            'Cache-Control' => 'no-cache',
        ]);
    }

    public function lexikAction()
    {
        global $kernel;

        if ('AppCache' === get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'sludio:lexik:clear',
        ]);
        $application->run($input);
        $data['success'] = 1;

        return new JsonResponse($data, 200, [
            'Cache-Control' => 'no-cache',
        ]);
    }
}
