<?php

namespace Sludio\HelperBundle\Position\Twig;

use Sludio\HelperBundle\Position\Service\PositionHandler;
use Sludio\HelperBundle\Script\Twig\TwigTrait;

class ObjectPositionExtension extends \Twig_Extension
{
    use TwigTrait;

    const NAME = 'position_object';

    /**
     * PositionHandler.
     */
    private $positionService;

    public function __construct(PositionHandler $positionService, $container)
    {
        $this->positionService = $positionService;
        $this->shortFunctions = $container->hasParameter('sludio_helper.script.short_functions') && $container->getParameter('sludio_helper.script.short_functions');
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::NAME;
    }

    public function getFunctions()
    {
        $input = [
            self::NAME => 'getter',
        ];

        return $this->makeArray($input, 'function');
    }

    public function getter($entity)
    {
        $getter = sprintf('get%s', ucfirst($this->positionService->getPositionFieldByEntity($entity)));

        return $entity->{$getter}();
    }
}
