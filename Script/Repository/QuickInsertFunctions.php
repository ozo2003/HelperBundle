<?php

namespace Sludio\HelperBundle\Script\Repository;

use AppCache;
use Sludio\HelperBundle\Script\Utils\Helper;

abstract class QuickInsertFunctions
{
    public static $entityManager;
    public static $connection;
    protected static $mock = [];
    protected static $metadata = [];
    protected static $tableName;
    protected static $identifier;

    protected static function buildExtra($extra)
    {
        $methods = [
            'GROUP BY',
            'HAVING',
            'ORDER BY',
        ];
        $sql = '';

        foreach ($methods as $method) {
            if (isset($extra[$method])) {
                $sql .= ' '.$method.' ';
                if (\is_array($extra[$method])) {
                    $sql .= implode(' ', $extra[$method]).' ';
                } else {
                    $sql .= $extra[$method].' ';
                }
            }
        }
        Filters::getLimit($extra, $sql);

        return Helper::oneSpace($sql);
    }

    protected static function buildWhere($tableName, array $where)
    {
        $whereSql = '';
        if (!empty($where)) {
            $path = ' WHERE ';
            foreach ($where as $key => $value) {
                if (!\is_array($value)) {
                    if (isset(self::$mock[$tableName][$key])) {
                        $whereSql .= $path.self::$mock[$tableName][$key].' = '.self::slashes($tableName, $key, $value);
                    } else {
                        $whereSql .= $path.$key.' = '.self::slashes($tableName, $key, $value);
                    }
                } else {
                    $whereSql .= $path.$value[0];
                }

                if ($value === reset($where)) {
                    $path = ' AND ';
                }
            }
        }

        return $whereSql;
    }

    protected static function slashes($tableName, $key, $value)
    {
        if ($value instanceof \DateTime) {
            $result = "'".addslashes(trim($value->format('Y-m-d H:i:s')))."'";
        } else {
            $result = self::numeric($tableName, $key, $value) ? (int)$value : "'".addslashes(trim($value))."'";
        }

        $trim = trim($result);
        if ($trim === '' || $trim === "''") {
            $result = null;
        }

        return $result;
    }

    protected static function numeric($tableName, $key, $value)
    {
        $intTypes = [
            'boolean',
            'integer',
            'longint',
        ];
        $flip = [];
        if (isset(self::$mock[$tableName])) {
            $flip = array_flip(self::$mock[$tableName]);
        }

        if (isset(self::$metadata[$tableName], $flip[$key])) {
            if (\in_array(self::$metadata[$tableName]->getFieldMapping($flip[$key])['type'], $intTypes, false)) {
                return true;
            }

            return false;
        }

        return \is_numeric($value) || \is_bool($value);
    }

    protected static function getTable(&$object, &$tableName, &$columns, &$type, $manager = null, array $extraFields = [])
    {
        self::init($manager);
        if (\is_object($object)) {
            self::extract($object);
            $tableName = self::$tableName;
            $columns = self::$mock[$tableName] ?: [];
            $type = 'object';
        } else {
            $tableName = $object['table_name'];
            unset($object['table_name']);
            $type = 'table';
            $columns = array_keys($object) ?: [];
        }

        if (isset($extraFields[$tableName])) {
            $columns = array_merge($columns, $extraFields[$tableName]);
        }
    }

    public static function init($manager = null)
    {
        if (self::$connection) {
            return;
        }
        global $kernel;

        if (AppCache::class === \get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        $container = $kernel->getContainer();

        $manager = $manager ?: $container->getParameter('sludio_helper.entity.manager');
        if (\is_object($manager)) {
            self::$entityManager = $manager;
        } else {
            self::$entityManager = $container->get('doctrine')->getManager($manager);
        }
        self::$connection = self::$entityManager->getConnection();
    }

    protected static function extract($object)
    {
        self::init(false);
        $data = Filters::extractExt(self::$entityManager->getMetadataFactory()->getMetadataFor(\get_class($object)));

        self::$mock = $data['mock'];
        self::$tableName = $data['table'];
        self::$metadata[$data['table']] = $data['meta'];
        self::$identifier = $data['identifier'];
    }

    protected static function parseUpdateResult($object, $type, $id, $tableName, array $result = null)
    {
        $data = [];
        if (!empty($result)) {
            foreach ($result as $key => $value) {
                $content = self::value($object, $key, $type, $tableName, false);
                if ($id && !\in_array($content, [
                        null,
                        $value,
                    ], true)) {
                    $data[$key] = $content;
                }
            }
        }

        return $data;
    }

    protected static function value($object, $variable, $type, $tableName, $check = true)
    {
        $value = null;
        if ($type === 'object') {
            $value = $object->{'get'.ucfirst(Helper::toCamelCase($variable))}();
        } else {
            if (isset($object[$variable])) {
                $value = $object[$variable];
            }
        }

        if ($check) {
            self::slashes($tableName, $variable, $value);
        }

        return $value;
    }

    protected static function makeValues($tableName, array $data = [])
    {
        $values = '';
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $values .= self::slashes($tableName, $key, $value).',';
            }
        }

        return \substr($values, 0, -1);
    }

    protected static function parsePersistColumns(array $columns = [], $object, $type, $tableName, &$idd)
    {
        $data = [];
        foreach ($columns as $value => $key) {
            $keys = [
                $key,
                $value,
            ];
            if (!Helper::multiple($keys)) {
                $value = self::value($object, $value, $type, $tableName);
                if ($value !== null) {
                    $data[$key] = $value;
                    if ($key === self::$identifier) {
                        $idd = $value;
                    }
                }
            }
        }

        return $data;
    }
}
