<?php

namespace Sludio\HelperBundle\Script\Controller;

use Predis\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sludio\HelperBundle\Translatable\Repository\TranslatableRepository as Sludio;

class ScriptController extends Controller
{
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

    public function redisAction()
    {
        return $this->runApp('sludio:redis:flush');
    }

    public function cacheAction()
    {
        $commands = [
            'clear',
            'warmup',
        ];
        foreach ($commands as $command) {
            $this->runApp('cache:'.$command, ['--env' => $this->container->get('kernel')->getEnvironment()], false);
        }

        return $this->result();
    }

    public function lexikAction()
    {
        return $this->runApp('sludio:lexik:clear');
    }

    public function generateAction()
    {
        Sludio::getAllTranslations();

        return $this->result();
    }
}
