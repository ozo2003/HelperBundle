<?php

namespace Sludio\HelperBundle\Repository\Translatable;

ini_set('memory_limit', '1024M');
ini_set('max_execution_time', 0);

class TranslatableRepository
{
    private static $redis;
    private static $em;
    private static $connection;

    private static function init()
    {
        global $kernel;

        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }

        self::$redis = $kernel->getContainer()->get('snc_redis.translations');
        self::$em = $kernel->getContainer()->get('doctrine')->getManager();
        self::$connection = self::$em->getConnection();
    }

    public static function getTranslations($class, $id)
    {
        self::init();
        $className = explode('\\', $class);
        $className = end($className);
        $redis = self::$redis;

        $result = unserialize($redis->get(strtolower($className).':translations:'.$id));
        $checked = unserialize($redis->get(strtolower($className).':translations:'.$id.':checked'));

        if (!$result && !$checked) {
            $connection = self::$connection;
            $sql = 'SELECT * FROM sludio_helper_translation WHERE object_class = :class AND foreign_key = :key';
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

            if ($result) {
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
        $sql = "SELECT foreign_key FROM sludio_helper_translation WHERE object_class = :class AND field = '{$field}' AND locale = :locale";
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
                    sludio_helper_translation
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
        $redis->del(strtolower($className).':translations:'.$id);
        $redis->del(strtolower($className).':translations:'.$id.':ckecked');
    }

    public static function findNextId($class)
    {
        self::init();
        $table = self::$em->getClassMetaData($class)->getTableName();
        $sql = "
            SHOW 
                TABLE STATUS 
            LIKE 
                '{$table}'
        ";
        $sth = self::$connection->prepare($sql);
        $sth->execute();
        $result = $sth->fetch();

        if (isset($result['Auto_increment'])) {
            return (int) $result['Auto_increment'];
        }

        return 1;
    }

    public static function removeTranslations($object, $em)
    {
        self::init();
        $class = get_class($object);
        $id = $object->getId();
        $connection = self::$connection;

        $sth = $connection->prepare('DELETE FROM `sludio_helper_translation` WHERE object_class = :class AND foreign_key = :key');
        $sth->bindValue('class', $class);
        $sth->bindValue('key', $id);
        $sth->execute();
    }
}
