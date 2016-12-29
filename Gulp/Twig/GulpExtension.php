<?php

namespace Sludio\HelperBundle\Gulp\Twig;

class GulpExtension extends \Twig_Extension
{
    private $paths = [];

    public function __construct($appDir, $kernel)
    {
        $this->appDir = $appDir;
        $this->kernel = $kernel;
    }
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('asset_version', array($this, 'getAssetVersion')),
        );
    }
    public function getName()
    {
        return 'sludio_browser.twig.gulp_extension';
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
