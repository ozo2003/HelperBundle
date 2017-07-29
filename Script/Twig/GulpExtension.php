<?php

namespace Sludio\HelperBundle\Script\Twig;

class GulpExtension extends \Twig_Extension
{
    private $paths = [];

    protected $appDir;
    protected $kernel;
    protected $short_functions;

    public function __construct($appDir, $kernel, $container)
    {
        $this->appDir = $appDir;
        $this->kernel = $kernel;

        $this->short_functions = $container->hasParameter('sludio_helper.script.short_functions') && $container->getParameter('sludio_helper.script.short_functions', false);
    }
    public function getFilters()
    {
        $array = array(
            new \Twig_SimpleFilter('sludio_asset_version', array($this, 'getAssetVersion')),
        );

        $short_array = array(
            new \Twig_SimpleFilter('asset_version', array($this, 'getAssetVersion')),
        );

        if ($this->short_functions) {
            return array_merge($array, $short_array);
        } else {
            return $array;
        }
    }
    public function getName()
    {
        return 'sludio_helper.twig.gulp_extension';
    }

    public function getAssetVersion($filename)
    {
        if (count($this->paths) === 0) {
            $manifestPath = $this->appDir.'/Resources/assets/rev-manifest.json';
            if (!file_exists($manifestPath)) {
                return $filename;
            }
            $this->paths = json_decode(file_get_contents($manifestPath), true);
            if (!isset($this->paths[$filename])) {
                return $filename;
            }
        }

        return $this->paths[$filename];
    }
}
