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

    public static function getDefaultLocale()
    {
        self::init();

        return self::$defaultLocale;
    }

    public static function init()
    {
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

    public static function getTranslations($class, $id)
    {
        self::init();
        $class = str_replace('Proxies\\__CG__\\', '', $class);
        $className = explode('\\', $class);
        $className = end($className);

        $result = [];
        $checked = false;
        if (self::$redis !== null) {
            $result = unserialize(self::$redis->get(strtolower($className).':translations:'.$id));
            $checked = unserialize(self::$redis->get(strtolower($className).':translations:'.$id.':checked'));
        }

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

            if ($result !== null && self::$redis !== null) {
                self::$redis->set(strtolower($className).':translations:'.$id, serialize($result));
                self::$redis->set(strtolower($className).':translations:'.$id.':checked', serialize(true));
            }
        }

        return $result;
    }

    public static function findByLocale($class, $locale, $content, $field = 'slug', $id = null, $id2 = null)
    {
        self::init();

        if (strlen($locale) == 2) {
            $locale = self::$localeArr[$locale];
        }

        $where = [
            'object_class' => $class,
            'locale' => $locale,
            'field' => $field,
        ];
        if ($id) {
            $where[] = ['foreign_key <> '.$id];
        }
        if ($id2) {
            $where['foreign_key'] = $id2;
        } else {
            $where['content'] = $content;
        }

        $result = Quick::get(new Translation(), false, $where, true, ['foreign_key']);

        return $result;
    }

    public static function updateTranslations($class, $locale, $field, $content, $id = 0)
    {
        $className = explode('\\', $class);
        $className = end($className);
        self::init();

        if (strlen($locale) == 2) {
            $locale = self::$localeArr[$locale];
        }

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

        if (self::$redis) {
            self::$redis->del(strtolower($className).':translations:'.$id);
            self::$redis->del(strtolower($className).':translations:'.$id.':ckecked');
        }
    }

    public static function removeTranslations($object)
    {
        self::init();
        $class = get_class($object);
        $id = $object->getId();

        $where = [
            'object_class' => $class,
            'foreign_key' => $id,
        ];
        Quick::delete(new Translation(), $where);
    }

    public static function getAllTranslations()
    {
        self::init();

        $data = Quick::get(new Translation(), false, [], true, ['*']);
        $result = [];
        foreach ($data as $row) {
            $result[$row['object_class']][$row['foreign_key']][$row['locale']][$row['field']] = $row['content'];
        }

        if (count($result)) {
            foreach ($result as $class => $objects) {
                $className = explode('\\', $class);
                $className = end($className);
                foreach ($objects as $id => $transl) {
                    self::$redis->set(strtolower($className).':translations:'.$id, serialize($transl));
                    self::$redis->set(strtolower($className).':translations:'.$id.':checked', serialize(true));
                }
            }
        }

        return $result;
    }
}
