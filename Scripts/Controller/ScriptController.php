<?php

namespace Sludio\HelperBundle\Scripts\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

class ScriptController extends Controller
{
    public function redisAction(Request $request)
    {
        $data['success'] = 1;
        foreach ($this->container->getParameter('sludio_helper.redis.managers') as $redis) {
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

        foreach (array('clear', 'warmup') as $command) {
            $application = new Application($kernel);
            $application->setAutoExit(false);
            $input = new ArrayInput(array(
               'command' => 'cache:'.$command,
               '--env' => $kernel->getEnvironment(),
            ));
            $application->run($input);
            $data['success'] = 1;
        }

        return new JsonResponse($data, 200, array(
            'Cache-Control' => 'no-cache',
        ));
    }
    
    public function ibrowsAction(Request $request){
        global $kernel;

        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        
        $application = new Application($kernel);
        $application->setAutoExit(false);
        
        $input = new ArrayInput(array(
           'command' => 'ibrows:sonatatranslationbundle:clearcache'
        ));
        $application->run($input);
        $data['success'] = 1;
        
        return new JsonResponse($data, 200, array(
            'Cache-Control' => 'no-cache',
        ));
    }
}
