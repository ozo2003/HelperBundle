<?php

namespace Sludio\HelperBundle\DependencyInjection\Requirement;

use Lexik\Bundle\TranslationBundle\LexikTranslationBundle;
use Sonata\AdminBundle\SonataAdminBundle;
use Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class Lexik extends AbstractRequirement
{
    protected $requirements = [
        LexikTranslationBundle::class => 'lexik/translation-bundle',
        SonataAdminBundle::class => 'sonata-project/admin-bundle',
        SonataDoctrineORMAdminBundle::class => 'sonata-project/doctrine-orm-admin-bundle',
    ];

    public function getRequirements()
    {
        return $this->requirements;
    }
}
