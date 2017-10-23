<?php

namespace Sludio\HelperBundle\Script\Twig;

class GulpExtension extends \Twig_Extension
{
    use TwigTrait;

    private $paths = [];

    protected $appDir;
    protected $kernel;

    public function __construct($appDir, $kernel, $container)
    {
        $this->appDir = $appDir;
        $this->kernel = $kernel;

        $this->shortFunctions = $container->hasParameter('sludio_helper.script.short_functions') && $container->getParameter('sludio_helper.script.short_functions');
    }

    public function getFilters()
    {
        $input = [
            'asset_version' => 'getAssetVersion',
        ];

        return $this->makeArray($input);
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
