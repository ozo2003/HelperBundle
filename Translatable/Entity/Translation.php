<?php

namespace Sludio\HelperBundle\Translatable\Entity;

/**
 * Translation.
 */
class Translation
{
    /**
     * @var int
     */
    private $id;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @var string
     */
    private $locale;

    /**
     * Set locale.
     *
     * @param string $locale
     *
     * @return Translation
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }
    /**
     * @var string
     */
    private $objectClass;

    /**
     * Set objectClass.
     *
     * @param string $objectClass
     *
     * @return Translation
     */
    public function setObjectClass($objectClass)
    {
        $this->objectClass = $objectClass;

        return $this;
    }

    /**
     * Get objectClass.
     *
     * @return string
     */
    public function getObjectClass()
    {
        return $this->objectClass;
    }
    /**
     * @var string
     */
    private $field;

    /**
     * Set field.
     *
     * @param string $field
     *
     * @return Translation
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Get field.
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }
    /**
     * @var int
     */
    private $foreignKey;

    /**
     * @var string
     */
    private $content;

    /**
     * Set foreignKey.
     *
     * @param int $foreignKey
     *
     * @return Translation
     */
    public function setForeignKey($foreignKey)
    {
        $this->foreignKey = $foreignKey;

        return $this;
    }

    /**
     * Get foreignKey.
     *
     * @return int
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * Set content.
     *
     * @param string $content
     *
     * @return Translation
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}
