<?php

namespace Sludio\HelperBundle\Entity\Translatable;

use Sludio\HelperBundle\Repository\Translatable\TranslatableRepository as Sludio;

class BaseEntity
{
    public function __construct()
    {
        if ($this->getId()) {
            $this->translates = $this->getTranslations();
        }
    }

    public $translates;

    public $localeArr = array(
        'lv' => 'lv_LV',
        'en' => 'en_US',
        'ru' => 'ru_RU',
    );

    public function __get($property)
    {
        $locale = strtolower(substr($property, -2));
        if (in_array($locale, array_keys($this->localeArr))) {
            $property = substr($property, 0, -2);

            return $this->getVariableByLocale($property, $this->localeArr[$locale]);
        }

        return $this->getVariableByLocale($property);
    }

    public function __set($property, $value)
    {
        $locale = strtolower(substr($property, -2));
        $property = substr($property, 0, -2);

        Sludio::updateTranslations(__CLASS__, $this->localeArr[$locale], $property, $value, $this->getId());
    }

    protected function getTranslations()
    {
        return Sludio::getTranslations(__CLASS__, $this->getId());
    }

    public function getVariableByLocale($variable, $locale = 'lv')
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
