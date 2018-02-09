<?php

namespace Sludio\HelperBundle\DependencyInjection\Requirement;

class Mobile extends AbstractRequirement
{
    /**
     * @var array
     */
    protected static $requirements = [
        \Mobile_Detect::class => 'mobiledetect/mobiledetectlib',
    ];

    public function getRequirements()
    {
        return self::$requirements;
    }
}
