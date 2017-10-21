<?php

namespace Sludio\HelperBundle\Translatable\Repository;

ini_set('memory_limit', '1024M');
ini_set('max_execution_time', 0);

use Sludio\HelperBundle\Script\Repository\QuickInsertRepository as Quick;
use Sludio\HelperBundle\Translatable\Entity\Translation;

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

    public static function getLocaleVar($locale)
    {
        return isset(self::$localeArr[$locale]) ? self::$localeArr[$locale] : $locale;
    }

    public static function getDefaultLocale()
    {
        self::init();

        return self::$defaultLocale;
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

        if ('AppCache' === get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        $container = $kernel->getContainer();
        self::$entityManager = $container->get('doctrine')->getManager();
        self::$defaultLocale = $container->getParameter('sludio_helper.translatable.default_locale');

        self::$redis = $container->get('snc_redis.'.$container->getParameter('sludio_helper.redis.translation'));
        self::$table = $container->getParameter('sludio_helper.translatable.table');
    }

    private static function getFromRedis($key, &$result, &$checked)
    {
        $result = [];
        $checked = false;
        if (self::$redis !== null) {
            $result = unserialize(self::$redis->get($key.':translations'));
            $checked = unserialize(self::$redis->get($key.':checked'));
        }
    }

    private static function setToRedis($key, $result)
    {
        if (!empty($result) && self::$redis !== null) {
            self::$redis->set($key.':translations', serialize($result));
            self::$redis->set($key.':checked', serialize(true));
        }
    }

    private static function delFromRedis($key)
    {
        if (self::$redis !== null) {
            self::$redis->del($key.':translations');
            self::$redis->del($key.':ckecked');
        }
    }

    public static function getTranslations($class, $id)
    {
        $class = str_replace('Proxies\\__CG__\\', '', $class);
        self::init($class, $className);

        $key = strtolower($className).':translations:'.$id;
        self::getFromRedis($key, $result, $checked);

        if (empty($result) && !$checked) {
            $data = Quick::get(new Translation(), false, [
                'object_class' => $class,
                'foreign_key' => $id,
            ], true, ['*']);
            if ($data !== null) {
                foreach ($data as $row) {
                    $result[$row['locale']][$row['field']] = $row['content'];
                }
            }

            self::setToRedis($key, $result);
        }

        return $result;
    }

    public static function findByLocale($class, $locale, $content, $field = 'slug', $notId = null, $isId = null)
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
        } else {
            $where['content'] = $content;
        }

        $result = Quick::get(new Translation(), false, $where, true, ['foreign_key']);

        return $result;
    }

    public static function updateTranslations($class, $locale, $field, $content, $id = 0)
    {
        self::init($class, $className);
        $locale = self::getLocaleVar($locale);

        if (!$id) {
            $id = Quick::findNextIdExt(new $class(), self::$entityManager);
            $update = 0;
        } else {
            $update = (int)self::findByLocale($class, $locale, $content, $field, null, $id);
        }

        $content = trim($content) != '' ? $content : null;

        $translation = new Translation();
        $translation->setField($field)
            ->setForeignKey($id)
            ->setLocale($locale)
            ->setObjectClass($class)
            ->setContent($content)
        ;

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

        $key = strtolower($className).':translations:'.$id;
        self::delFromRedis($key);

        self::getTranslations($class, $id);
    }

    public static function removeTranslations($object)
    {
        $class = get_class($object);
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
        $classes = Quick::get(new Translation(), false, [], true, ['object_class'], null, ['MODE' => 'DISTINCT']);
        foreach ($classes as $class) {
            $ids = Quick::get(new Translation(), false, ['object_class' => $class], true, ['foreign_key'], null, ['MODE' => 'DISTINCT']);
            foreach ($ids as $id) {
                self::getTranslations($class, $id);
            }
        }
    }
}
