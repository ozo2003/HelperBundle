<?php

namespace Sludio\HelperBundle\DependencyInjection\Requirement;

use JMS\I18nRoutingBundle\JMSI18nRoutingBundle;
use Lexik\Bundle\TranslationBundle\LexikTranslationBundle;
use Sonata\AdminBundle\SonataAdminBundle;
use Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle;

class Translatable extends AbstractRequirement
{
    /**
     * @var array
     */
    protected static $requirements = [
        LexikTranslationBundle::class => 'lexik/translation-bundle',
        SonataAdminBundle::class => 'sonata-project/admin-bundle',
        SonataDoctrineORMAdminBundle::class => 'sonata-project/doctrine-orm-admin-bundle',
        JMSI18nRoutingBundle::class => 'jms/i18n-routing-bundle',
    ];

    public function getRequirements()
    {
        return self::$requirements;
    }
}
