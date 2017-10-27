<?php

namespace Sludio\HelperBundle\Script\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ScriptController extends Controller
{
    use ControllerTrait;

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
        return $this->runApp('sludio:translations:generate');
    }
}
