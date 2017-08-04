<?php

namespace Sludio\HelperBundle\Translatable\Helper;

use Symfony\Bridge\Doctrine\RegistryInterface as Registry;
use Symfony\Component\Form\Form as Form;

class Manager
{
    protected $em;

    public function __construct(Registry $reg)
    {
        $this->em = $reg->getManager();
    }

    private function getField($entity, $field, $locale)
    {
        return $entity->getVariableByLocale($field, $locale);
    }

    private function setField($entity, $field, $value)
    {
        $setterFunctionName = 'set'.$field;
        $entity->{$setterFunctionName}($value);
    }

    public function getTranslatedFields($class, $field, $id, $locales, $userLocale)
    {
        $em = $this->em;
        $entity = $em->getRepository($class)->find($id);

        $translated;
        foreach ($locales as $locale) {
            $translated[$locale][$field] = $this->getField($entity, $field, $locale);
        }

        return $translated;
    }

    public function getNewTranslatedFields($class, $field, $locales, $userLocale)
    {
        $translated;
        foreach ($locales as $locale) {
            $translated[$locale][$field] = '';
        }

        return $translated;
    }

    public function persistTranslations(Form $form, $class, $field, $id, $locales, $userLocale)
    {
        $translations = $form->getData();

        $em = $this->em;
        $repository = $em->getRepository($class);
        if (!$id) {
            $entity = new $class();
        } else {
            $entity = $repository->find($id);
        }

        foreach ($locales as $locale) {
            if (array_key_exists($locale, $translations) && ($translations[$locale] !== null)) {
                $postedValue = $translations[$locale];
                $storedValue = $this->getField($entity, $field, $locale);
                if ($storedValue !== $postedValue) {
                    $lang = explode('_', $locale);
                    $fieldName = $field.ucfirst(reset($lang));
                    $entity->__set($fieldName, $postedValue);
                }
            }
        }
    }
}
