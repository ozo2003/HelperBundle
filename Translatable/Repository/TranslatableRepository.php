<?php

namespace Sludio\HelperBundle\Translatable\Repository;

use Sludio\HelperBundle\Usort\Repository\UsortRepository;

ini_set('memory_limit', '1024M');
ini_set('max_execution_time', 0);

class TranslatableRepository extends UsortRepository
{
    public static $redis;
    public static $table;

    public static function init()
    {
        parent::init();
        self::$redis = self::$container->getParameter('sludio_helper.redis.translation');
        self::$table = self::$container->getParameter('sludio_helper.translatable.table');
    }

    public static function getTranslations($class, $id)
    {
        self::init();
        $className = explode('\\', $class);
        $className = end($className);
        $redis = self::$redis;

        $result = $redis ? unserialize($redis->get(strtolower($className).':translations:'.$id)) : nul;
        $checked = $redis ? unserialize($redis->get(strtolower($className).':translations:'.$id.':checked')) : null;

        if (!$result && !$checked) {
            $connection = self::$connection;
            $sql = 'SELECT * FROM '.self::$table.' WHERE object_class = :class AND foreign_key = :key';
            $sth = $connection->prepare($sql);
            $options = array(
                'class' => $class,
                'key' => $id,
            );
            $sth->execute($options);
            $result = array();
            while ($row = $sth->fetch()) {
                $result[$row['locale']][$row['field']] = $row['content'];
            }

            if ($result && $redis) {
                $redis->set(strtolower($className).':translations:'.$id, serialize($result));
                $redis->set(strtolower($className).':translations:'.$id.':checked', serialize(true));
            }
        }

        return $result;
    }

    public static function findByLocale($class, $locale, $content, $field = 'slug', $id = null, $id2 = null)
    {
        self::init();
        $connection = self::$connection;
        $options = array(
            'class' => $class,
            'locale' => $locale,
        );
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
            $id = self::findNextId($class);
        }

        $res = (int) self::findByLocale($class, $locale, $content, $field, null, $id);
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
                    sludio_helper_translation
                        (content, object_class, locale, field, foreign_key)
                VALUES
                    (:content,'{$class}', '{$locale}','{$field}',{$id})
            ";
        }
        $connection = self::$connection;
        $sth = $connection->prepare($sql);
        $sth->bindValue('content', $content);
        $sth->execute();

        $redis = self::$redis;
        if ($redis) {
            $redis->del(strtolower($className).':translations:'.$id);
            $redis->del(strtolower($className).':translations:'.$id.':ckecked');
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
        $redis = self::$redis;

        $connection = self::$connection;
        $sql = 'SELECT * FROM '.self::$table;
        $sth = $connection->prepare($sql);
        $sth->execute();
        $result = array();
        while ($row = $sth->fetch()) {
            $result[$row['object_class']][$row['foreign_key']][$row['locale']][$row['field']] = $row['content'];
        }

        foreach ($result as $class => $objects) {
            $className = explode('\\', $class);
            $className = end($className);
            foreach ($objects as $id => $transl) {
                $redis->set(strtolower($className).':translations:'.$id, serialize($transl));
                $redis->set(strtolower($className).':translations:'.$id.':checked', serialize(true));
            }
        }

        return $result;
    }
}
