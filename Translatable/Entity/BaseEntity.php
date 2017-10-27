<?php

namespace Sludio\HelperBundle\Translatable\Entity;

use Sludio\HelperBundle\Script\Utils\Helper;
use Sludio\HelperBundle\Translatable\Repository\TranslatableRepository as Sludio;

abstract class BaseEntity
{
    public $className;
    public $translates;

    public $localeArr = [
        'lv' => 'lv_LV',
        'en' => 'en_US',
        'ru' => 'ru_RU',
    ];

    abstract public function getId();

    public function __construct()
    {
        $this->translates = $this->getTranslations();
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

    public function getLocaleVar($locale)
    {
        return isset($this->localeArr[$locale]) ? $this->localeArr[$locale] : $locale;
    }

    private function check($locale)
    {
        return in_array($locale, array_keys($this->localeArr));
    }

    public function __get($property)
    {
        if (!method_exists($this, 'get'.ucfirst($property))) {
            $locale = Sludio::getDefaultLocale();
        } else {
            $locale = strtolower(substr($property, -2));
            $property = substr($property, 0, -2);
        }

        if ($this->check($locale)) {
            return $this->getVariableByLocale($property, $this->localeArr[$locale]);
        }

        return $this->{$property};
    }

    public function __set($property, $value)
    {
        if (!method_exists($this, 'set'.ucfirst($property))) {
            $locale = strtolower(substr($property, -2));
            if ($this->check($locale)) {
                $property = substr($property, 0, -2);
                Sludio::updateTranslations(get_class($this), $this->localeArr[$locale], $property, $value, $this->getId());
            }
        }
        $this->{$property} = $value;

        return $this;
    }

    protected function getTranslations()
    {
        if ($this->getId()) {
            return Sludio::getTranslations(get_called_class(), $this->getId());
        }

        return null;
    }

    public function getVariableByLocale($variable, $locale = null, $returnOriginal = false)
    {
        $locale = $this->getLocaleVar($locale ?: Sludio::getDefaultLocale());

        if (!$this->translates && $this->getId()) {
            $this->translates = $this->getTranslations();
        }

        if (isset($this->translates[$locale][$variable])) {
            return $this->translates[$locale][$variable];
        }

        if ($returnOriginal) {
            return $this->{'get'.Helper::toCamelCase($variable)}();
        }

        return '';
    }

    public function cleanText($text)
    {
        return Helper::cleanText($text);
    }
}
