<?php

namespace Sludio\HelperBundle\DependencyInjection\Requirement;

use Lexik\Bundle\TranslationBundle\LexikTranslationBundle;
use Sonata\AdminBundle\SonataAdminBundle;
use Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle;
use JMS\I18nRoutingBundle\JMSI18nRoutingBundle;

class Translatable extends AbstractRequirement
{
    protected $requirements = [
        LexikTranslationBundle::class => 'lexik/translation-bundle',
        SonataAdminBundle::class => 'sonata-project/admin-bundle',
        SonataDoctrineORMAdminBundle::class => 'sonata-project/doctrine-orm-admin-bundle',
        JMSI18nRoutingBundle::class => 'jms/i18n-routing-bundle'
    ];

    public function getRequirements()
    {
        return $this->requirements;
    }
}
