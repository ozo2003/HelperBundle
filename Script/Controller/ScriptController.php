<?php

namespace Sludio\HelperBundle\Script\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sludio\HelperBundle\Sitemap\Dumper\MemoryDumper;

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

    public function sitemapAction()
    {
        $format = $this->container->getParameter('sludio_helper.sitemap.format');
        $type = $this->container->getParameter('sludio_helper.sitemap.type');
        $sitemap = $this->container->get("sludio_helper.sitemap.{$format}.{$type}");
        $sitemap->setDumper(new MemoryDumper());

        return $this->resultXml($sitemap->build());
    }

    public function sitemapGenerateAction()
    {
        return $this->runApp('sludio:sitemap:generate');
    }
}
