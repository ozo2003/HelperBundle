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

    public function __construct()
    {
        $this->translates = $this->getTranslations();
        $this->getClassName();
    }

    protected function getTranslations($skip = false)
    {
        return $this->getId() ? Sludio::getTranslations(\get_class($this), $this->getId(), $skip) : null;
    }

    abstract public function getId();

    public function getClassName()
    {
        if (!$this->className) {
            $className = explode('\\', \get_called_class());
            $this->className = strtolower(end($className));
        }

        return $this->className;
    }

    public function __call($name, $arguments)
    {
        $pos = strpos($name, '_');
        if ($pos !== false) {
            $locale = strtolower(substr($name, $pos + 1));
            if (\count($arguments) === 0 && $this->check($locale) === true) {
                return $this->__get($name);
            }
        }
    }

    protected function check($locale)
    {
        return array_key_exists($locale, $this->localeArr);
    }

    protected function notExist($method, $pos)
    {
        return !method_exists($this, $method) && $pos !== false;
    }

    public function __get($property)
    {
        $getter = 'get'.ucfirst($property);

        $pos = strpos($property, '_');
        if ($this->notExist($getter, $pos)) {
            $locale = strtolower(substr($property, $pos + 1));
            $property = substr($property, 0, -3);

            if ($this->check($locale)) {
                return $this->getVariableByLocale($property, $this->getLocaleVar($locale));
            }
        }

        return $this->{$getter}();
    }

    public function __set($property, $value)
    {
        $pos = strpos($property, '_');
        $setter = 'set'.ucfirst($property);

        if ($this->notExist($setter, $pos)) {
            $locale = strtolower(substr($property, $pos + 1));
            $property = substr($property, 0, -3);

            if ($this->check($locale)) {
                Sludio::updateTranslations(\get_class($this), $this->getLocaleVar($locale), $property, $value, $this->getId());
                $this->getTranslations();
            }
        }
        $this->{$property} = $value;

        return $this;
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

    public function getLocaleVar($locale)
    {
        return $this->check($locale) ? $this->localeArr[$locale] : $locale;
    }

    public function cleanText($text)
    {
        return Helper::cleanText($text);
    }
}
