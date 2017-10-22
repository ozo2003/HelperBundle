<?php

namespace Sludio\HelperBundle\Translatable\Helper;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use Sludio\HelperBundle\Translatable\Entity\BaseEntity;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormInterface;

class Manager
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(RegistryInterface $registry)
    {
        $this->entityManager = $registry->getManager();
    }

    private function getField(BaseEntity $entity, $field, $locale)
    {
        return $entity->getVariableByLocale($field, $locale);
    }

    private function setField($entity, $field, $value)
    {
        $setterFunctionName = 'set'.$field;
        $entity->{$setterFunctionName}($value);
    }

    public function getTranslatedFields($class, $field, $identifier, $locales)
    {
        $entity = $this->entityManager->getRepository($class)->find($identifier);

        $translated = [];
        foreach ($locales as $locale) {
            $translated[$locale][$field] = $this->getField($entity, $field, $locale);
        }

        return $translated;
    }

    public function getNewTranslatedFields($field, $locales)
    {
        $translated = [];
        foreach ($locales as $locale) {
            $translated[$locale][$field] = '';
        }

        return $translated;
    }

    public function persistTranslations(FormInterface $form, $class, $field, $identifier, $locales)
    {
        $translations = $form->getData();

        $repository = $this->entityManager->getRepository($class);
        /** @var $entity BaseEntity */
        if (!$identifier) {
            $entity = new $class();
        } else {
            $entity = $repository->find($identifier);
        }

        foreach ($locales as $locale) {
            if (array_key_exists($locale, $translations) && ($translations[$locale] !== null)) {
                $postedValue = $translations[$locale];
                if ($this->getField($entity, $field, $locale) !== $postedValue) {
                    $lang = explode('_', $locale);
                    $entity->__set($field.ucfirst(reset($lang)), $postedValue);
                }
            }
        }
    }
}
