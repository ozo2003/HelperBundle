<?php

namespace Sludio\HelperBundle\Translatable\Repository;

ini_set('memory_limit', '1024M');
ini_set('max_execution_time', 0);

use Sludio\HelperBundle\Insert\Repository\QuickInsertRepository as Quick;

class TranslatableRepository
{
    public static $em;
    public static $connection;
    public static $container;

    public static $redis;
    public static $table;

    public static $localeArr = [
        'lv' => 'lv_LV',
        'en' => 'en_US',
        'ru' => 'ru_RU',
    ];

    public static function getDefaultLocale()
    {
        self::init();

        return self::$container->getParameter('sludio_helper.translatable.default_locale');
    }

    public static function init()
    {
        global $kernel;

        if ('AppCache' === get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        self::$container = $kernel->getContainer();

        self::$em = self::$container->get('doctrine')
            ->getManager(self::$container->getParameter('sludio_helper.translatable.manager'))
        ;
        self::$connection = self::$em->getConnection();

        self::$redis = self::$container->get('snc_redis.'.self::$container->getParameter('sludio_helper.redis.translation'));
        self::$table = self::$container->getParameter('sludio_helper.translatable.table');
    }

    public static function getTranslations($class, $id)
    {
        self::init();
        $className = explode('\\', $class);
        $className = end($className);

        $result = self::$redis ? unserialize(self::$redis->get(strtolower($className).':translations:'.$id)) : null;
        $checked = self::$redis ? unserialize(self::$redis->get(strtolower($className).':translations:'.$id.':checked')) : null;

        if (!$result && !$checked) {
            $connection = self::$connection;
            $sql = 'SELECT * FROM '.self::$table.' WHERE object_class = :class AND foreign_key = :key';
            $sth = $connection->prepare($sql);
            $options = [
                'class' => $class,
                'key' => $id,
            ];
            $sth->execute($options);
            $result = [];
            while ($row = $sth->fetch()) {
                $result[$row['locale']][$row['field']] = $row['content'];
            }

            if ($result && self::$redis) {
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

        $connection = self::$connection;
        $options = [
            'class' => $class,
            'locale' => $locale,
        ];
        $sql = "SELECT foreign_key FROM ".self::$table." WHERE object_class = :class AND field = '{$field}' AND locale = :locale";
        if ($id) {
            $sql .= ' AND foreign_key <> :id';
            $options['id'] = $id;
        }
        if ($id2) {
            $sql .= ' AND foreign_key = :id';
            $options['id'] = $id2;
        } else {
            $sql .= ' AND content = :content';
            $options['content'] = $content;
        }
        $sth = $connection->prepare($sql);
        $sth->execute($options);
        $result = $sth->fetchAll();
        if (isset($result[0])) {
            return $result;
        }

        return null;
    }

    public static function updateTranslations($class, $locale, $field, $content, $id = 0)
    {
        $className = explode('\\', $class);
        $className = end($className);
        self::init();

        if (!$id) {
            $id = self::findNextId(new $class());
        }

        if (strlen($locale) == 2) {
            $locale = self::$localeArr[$locale];
        }

        $res = (int)self::findByLocale($class, $locale, $content, $field, null, $id);
        $class = str_replace('\\', '\\\\', $class);
        $content = trim($content) != '' ? $content : null;
        if ($res) {
            $sql = "
                UPDATE
                    ".self::$table."
                SET
                    content = :content
                WHERE
                    object_class = '{$class}'
                AND
                    locale = '{$locale}'
                AND
                    field = '{$field}'
                AND
                    foreign_key = {$id}
            ";
        } else {
            $sql = "
                INSERT INTO
                    ".self::$table."
                        (content, object_class, locale, field, foreign_key)
                VALUES
                    (:content,'{$class}', '{$locale}','{$field}',{$id})
            ";
        }
        $connection = self::$connection;
        $sth = $connection->prepare($sql);
        $sth->bindValue('content', $content);
        $sth->execute();

        if (self::$redis) {
            self::$redis->del(strtolower($className).':translations:'.$id);
            self::$redis->del(strtolower($className).':translations:'.$id.':ckecked');
        }
    }

    public static function removeTranslations($object, $em)
    {
        self::init();
        $class = get_class($object);
        $id = $object->getId();
        $connection = self::$connection;

        $sth = $connection->prepare('DELETE FROM '.self::$table.' WHERE object_class = :class AND foreign_key = :key');
        $sth->bindValue('class', $class);
        $sth->bindValue('key', $id);
        $sth->execute();
    }

    public static function getAllTranslations()
    {
        self::init();

        $connection = self::$connection;
        $sql = 'SELECT * FROM '.self::$table;
        $sth = $connection->prepare($sql);
        $sth->execute();
        $result = [];
        while ($row = $sth->fetch()) {
            $result[$row['object_class']][$row['foreign_key']][$row['locale']][$row['field']] = $row['content'];
        }

        foreach ($result as $class => $objects) {
            $className = explode('\\', $class);
            $className = end($className);
            foreach ($objects as $id => $transl) {
                self::$redis->set(strtolower($className).':translations:'.$id, serialize($transl));
                self::$redis->set(strtolower($className).':translations:'.$id.':checked', serialize(true));
            }
        }

        return $result;
    }

    public static function findNextId($object)
    {
        $data = Quick::extractExt($object, self::$em);
        $sql = "
            SELECT
                AUTO_INCREMENT
            FROM
                information_schema.tables
            WHERE
                table_name = '".$data['table']."'
            AND
                table_schema = DATABASE()
        ";
        $sth = self::$connection->prepare($sql);
        $sth->execute();
        $result = $sth->fetch();

        if (isset($result['AUTO_INCREMENT'])) {
            return (int)$result['AUTO_INCREMENT'];
        }

        return 1;
    }
}
