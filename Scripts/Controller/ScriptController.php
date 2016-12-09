<?php

namespace Sludio\HelperBundle\SludioHelperBundle\Scripts\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ScriptController extends Controller
{
    public function redisAction()
    {
        $data['success'] = 1;
        $this->get('snc_redis.cache')->flushdb();
        $this->get('snc_redis.translations')->flushdb();
        $this->get('snc_redis.session')->flushdb();
        $this->get('snc_redis.meta')->flushdb();

        return new JsonResponse($data, 200, array(
            'Cache-Control' => 'no-cache',
        ));
    }

    public function cacheAction()
    {
        global $kernel;

        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }

        foreach (array('clear', 'warmup') as $com) {
            $application = new Application($kernel);
            $application->setAutoExit(false);
            $input = new ArrayInput(array(
               'command' => 'cache:'.$com,
               '--env' => $kernel->getEnvironment(),
            ));
            $application->run($input);
            $data['success'] = 1;
        }

        return new JsonResponse($data, 200, array(
            'Cache-Control' => 'no-cache',
        ));
    }
}
