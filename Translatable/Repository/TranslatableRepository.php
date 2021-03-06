<?php

namespace Sludio\HelperBundle\Translatable\Repository;

use Sludio\HelperBundle\Script\Repository\QuickInsertRepository as Quick;
use Sludio\HelperBundle\Translatable\Entity\BaseEntity;
use Sludio\HelperBundle\Translatable\Entity\Translation;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;

class TranslatableRepository
{
    public static $defaultLocale;

    public static $redis;
    public static $table;
    public static $entityManager;

    public static $localeArr = [
        'lv' => 'lv_LV',
        'en' => 'en_US',
        'ru' => 'ru_RU',
    ];

    public static function updateTranslations($class, $locale, $field, $content, $id = 0)
    {
        self::init($class, $className);
        $locale = self::getLocaleVar($locale);

        $doUpdate = false;
        if ((int)$id === 0) {
            $doUpdate = true;
            $id = Quick::findNextIdExt(self::$entityManager->getMetadataFactory()->getMetadataFor($class));
        }

        $update = (int)self::findByLocale($class, $locale, $content, $field, null, $id);
        if ($update === 0 && $doUpdate === true) {
            $id = Quick::findNextIdExt(self::$entityManager->getMetadataFactory()->getMetadataFor($class));
        }

        $content = trim($content) !== '' ? $content : null;

        $translation = new Translation();
        // @formatter:off
        $translation->setField($field)
            ->setForeignKey((int)$id)
            ->setLocale($locale)
            ->setObjectClass($class)
            ->setContent($content);
        // @formatter:on

        if ($update === 0) {
            Quick::persist($translation);
        } else {
            $where = [
                'field' => $field,
                'foreign_key' => $id,
                'object_class' => $class,
                'locale' => $locale,
            ];
            $tId = Quick::get(new Translation(), true, $where);
            Quick::update($tId, $translation);
        }

        self::getTranslations($class, $id, true);
    }

    public static function init($class = null, &$className = null)
    {
        if ($class) {
            $class = explode('\\', $class);
            $className = end($class);
        }
        if (self::$redis) {
            return;
        }
        global $kernel;

        if ($kernel instanceof HttpCache) {
            $kernel = $kernel->getKernel();
        }
        $container = $kernel->getContainer();
        self::$entityManager = $container->get('doctrine')->getManager();
        self::$defaultLocale = $container->getParameter('sludio_helper.translatable.default_locale');

        self::$redis = $container->get('snc_redis.'.$container->getParameter('sludio_helper.redis.translation'));
        self::$table = $container->getParameter('sludio_helper.translatable.table');
    }

    public static function getLocaleVar($locale)
    {
        return isset(self::$localeArr[$locale]) ? self::$localeArr[$locale] : $locale;
    }

    public static function findByLocale($class, $locale, $content = null, $field = 'slug', $notId = null, $isId = null)
    {
        self::init();
        $locale = self::getLocaleVar($locale ?: self::getDefaultLocale());

        $where = [
            'object_class' => $class,
            'locale' => $locale,
            'field' => $field,
        ];
        if ($notId) {
            $where[] = ['foreign_key <> '.$notId];
        }

        if ($isId) {
            $where['foreign_key'] = $isId;
        } elseif ($content !== null) {
            $where['content'] = $content;
        }

        return Quick::get(new Translation(), false, $where, ['foreign_key']);
    }

    public static function getDefaultLocale()
    {
        self::init();

        return self::$defaultLocale;
    }

    public static function getTranslations($class, $id, $skip = false)
    {
        $class = str_replace('Proxies\\__CG__\\', '', $class);
        self::init($class, $className);

        $key = strtolower($className).':translations:'.$id;
        $result = [];
        $checked = false;
        if ($skip === false) {
            self::getFromRedis($key, $result, $checked);
        } else {
            self::delFromRedis($key);
        }

        if (empty($result) && !$checked) {
            $data = Quick::get(new Translation(), false, [
                'object_class' => $class,
                'foreign_key' => $id,
            ], ['*']);
            if ($data !== null) {
                /** @var $data array */
                foreach ($data as $row) {
                    $result[$row['locale']][$row['field']] = $row['content'];
                }
            }

            self::setToRedis($key, $result);
        }

        return $result;
    }

    private static function getFromRedis($key, &$result, &$checked)
    {
        if (self::$redis !== null) {
            $result = unserialize(self::$redis->get(self::tKey($key)));
            $checked = unserialize(self::$redis->get(self::cKey($key)));
        }
    }

    private static function tKey(&$key)
    {
        return $key.':translations';
    }

    private static function cKey(&$key)
    {
        return $key.':checked';
    }

    private static function delFromRedis($key)
    {
        if (self::$redis !== null) {
            self::$redis->del(self::tKey($key));
            self::$redis->del(self::cKey($key));
        }
    }

    private static function setToRedis($key, $result)
    {
        if (!empty($result) && self::$redis !== null) {
            self::$redis->set(self::tKey($key), serialize($result));
            self::$redis->set(self::cKey($key), serialize(true));
        }
    }

    public static function removeTranslations(BaseEntity $object)
    {
        $class = \get_class($object);
        self::init($class, $className);
        $id = $object->getId();

        $where = [
            'object_class' => $class,
            'foreign_key' => $id,
        ];
        Quick::delete(new Translation(), $where);
        $key = strtolower($className).':translations:'.$id;
        self::delFromRedis($key);
    }

    public static function getAllTranslations()
    {
        self::init();
        $classes = Quick::get(new Translation(), false, [], ['object_class'], null, ['MODE' => 'DISTINCT']);
        if (!empty($classes)) {
            /** @var $classes array */
            foreach ($classes as $class) {
                $ids = Quick::get(new Translation(), false, ['object_class' => $class], ['foreign_key'], null, ['MODE' => 'DISTINCT']);
                if (!empty($ids)) {
                    /** @var $ids array */
                    foreach ($ids as $id) {
                        self::getTranslations($class, $id, true);
                    }
                }
            }
        }
    }
}
