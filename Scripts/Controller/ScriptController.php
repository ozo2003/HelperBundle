<?php

namespace Sludio\HelperBundle\SludioHelperBundle\Scripts\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ScriptController extends Controller
{
    public function redisAction(Request $request)
    {
        $data['success'] = 1;
        foreach ($this->container->getParameter('sludio_helper.redis') as $redis) {
            $this->get('snc_redis.'.$redis)->flushdb();
        }

        return new JsonResponse($data, 200, array(
            'Cache-Control' => 'no-cache',
        ));
    }

    public function cacheAction(Request $request)
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
