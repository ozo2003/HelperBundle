<?php

namespace Sludio\HelperBundle\Translatable\Helper;

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

    public function getTranslatedFields($class, $field, $identifier, $locales)
    {
        $entity = $this->entityManager->getRepository($class)->find($identifier);

        $translated = [];
        /** @var $locales array */
        foreach ($locales as $locale) {
            $translated[$locale][$field] = $this->getField($entity, $field, $locale);
        }

        return $translated;
    }

    private function getField(BaseEntity $entity, $field, $locale)
    {
        return $entity->getVariableByLocale($field, $locale);
    }

    public function getNewTranslatedFields($field, $locales)
    {
        $translated = [];
        /** @var $locales array */
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

        /** @var $locales array */
        foreach ($locales as $locale) {
            if (array_key_exists($locale, $translations) && ($translations[$locale] !== null)) {
                $postedValue = $translations[$locale];
                if ($this->getField($entity, $field, $locale) !== $postedValue) {
                    $lang = explode('_', $locale);
                    $entity->__set($field.'_'.strtolower(reset($lang)), $postedValue);
                }
            }
        }
    }

    protected function setField(BaseEntity $entity, $field, $value)
    {
        $setterFunctionName = 'set'.$field;
        $entity->{$setterFunctionName}($value);
    }
}
