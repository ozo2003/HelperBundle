<?php

namespace Sludio\HelperBundle\SludioHelperBundle\Controller\Scripts;

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

        $envs = array(
            'dev',
            'prod',
        );

        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }

        foreach ($envs as $env) {
            $application = new Application($kernel);
            $application->setAutoExit(false);
            $input = new ArrayInput(array(
               'command' => 'cache:clear',
               '--env' => $env,
            ));
            $application->run($input);
        }
        $data['success'] = 1;

        return new JsonResponse($data, 200, array(
            'Cache-Control' => 'no-cache',
        ));
    }
}
