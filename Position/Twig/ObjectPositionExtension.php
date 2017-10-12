<?php

namespace Sludio\HelperBundle\Position\Twig;

use Sludio\HelperBundle\Position\Service\PositionHandler;
use Twig_Extension;
use Twig_SimpleFunction;

class ObjectPositionExtension extends Twig_Extension
{
    const NAME = 'sludio_position_object';

    /**
     * PositionHandler.
     */
    private $positionService;

    public function __construct(PositionHandler $positionService)
    {
        $this->positionService = $positionService;
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
        return [
            new Twig_SimpleFunction(self::NAME, function ($entity) {
                $getter = sprintf('get%s', ucfirst($this->positionService->getPositionFieldByEntity($entity)));

                return $entity->{$getter}();
            }),
        ];
    }
}
