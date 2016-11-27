<?php

namespace Sludio\HelperBundle\Entity\Translatable;

use Sludio\HelperBundle\Repository\Translatable\TranslatableRepository as Sludio;

class BaseEntity
{
    public $className;

    public function __construct()
    {
        if ($this->getId()) {
            $this->translates = $this->getTranslations();
        }
        $this->getClassName();
    }

    public function getClassName()
    {
        if (!$this->className) {
            $className = explode('\\', get_called_class());
            $this->className = strtolower(end($className));
        }

        return $this->className;
    }

    public $translates;

    public $localeArr = array(
        'lv' => 'lv_LV',
        'en' => 'en_US',
        'ru' => 'ru_RU',
    );

    public function __get($property)
    {
        if (!method_exists($this, 'get'.ucfirst($property))) {
            $locale = 'en';
        } else {
            $locale = strtolower(substr($property, -2));
            $property = substr($property, 0, -2);
        }

        if (in_array($locale, array_keys($this->localeArr))) {
            return $this->getVariableByLocale($property, $this->localeArr[$locale]);
        }

        return $this->{$property};
    }

    public function __set($property, $value)
    {
        $locale = strtolower(substr($property, -2));
        if (in_array($locale, array_keys($this->localeArr))) {
            $property = substr($property, 0, -2);
            Sludio::updateTranslations(get_called_class(), $this->localeArr[$locale], $property, $value, $this->getId());
        }
        $this->{$property} = $value;

        return $this;
    }

    protected function getTranslations()
    {
        return Sludio::getTranslations(get_called_class(), $this->getId());
    }

    public function getVariableByLocale($variable, $locale = 'en')
    {
        if (!$this->translates && $this->getId()) {
            $this->translates = $this->getTranslations();
        }

        if (strlen($locale) == 2) {
            $locale = $this->localeArr[$locale];
        }

        if (isset($this->translates[$locale][$variable])) {
            return $this->translates[$locale][$variable];
        }

        return '';
    }
}
