<?php

namespace Sludio\HelperBundle\Script\Twig;

class MissingExtension extends \Twig_Extension
{
    use TwigTrait;

    protected $request;
    protected $entityManager;

    public function __construct($requestStack, $entityManager, $container)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->entityManager = $entityManager;

        $this->shortFunctions = $container->hasParameter('sludio_helper.script.short_functions') && $container->getParameter('sludio_helper.script.short_functions');
    }

    public function getName()
    {
        return 'sludio_helper.twig.missing_extension';
    }

    public function getFilters()
    {
        $input = [
            'objects' => 'getObjects',
            'svg' => 'getSvg',
        ];

        return $this->makeArray($input);
    }

    public function getObjects($class, $variable, $order = null, $one = false)
    {
        $variable = is_array($variable) ? $variable : [$variable];
        $order = is_array($order) ? $order : [$order];

        if ($one) {
            $objects = $this->entityManager->getRepository($class)->findOneBy($variable, $order);
        } else {
            $objects = $this->entityManager->getRepository($class)->findBy($variable, $order);
        }

        return $objects;
    }

    public function getSvg($svg)
    {
        return file_get_contents(getcwd().$svg);
    }
}
