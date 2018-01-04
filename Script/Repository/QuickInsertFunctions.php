<?php

namespace Sludio\HelperBundle\Script\Repository;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Sludio\HelperBundle\Script\Utils\Helper;

abstract class QuickInsertFunctions
{
    protected static $mock = [];
    protected static $metadata = [];
    protected static $tableName;
    protected static $identifier;

    public static $entityManager;
    public static $connection;

    public static function init($manager = null)
    {
        if (self::$connection) {
            return;
        }
        global $kernel;

        if ('AppCache' === \get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        $container = $kernel->getContainer();

        $manager = $manager ?: $container->getParameter('sludio_helper.entity.manager');
        if(\is_object($manager)){
            self::$entityManager = $manager;
        } else {
            self::$entityManager = $container->get('doctrine')->getManager($manager);
        }
        self::$connection = self::$entityManager->getConnection();
    }

    protected static function extract($object)
    {
        self::init(false);
        $data = self::extractExt(self::$entityManager->getMetadataFactory()->getMetadataFor(\get_class($object)));

        self::$mock = $data['mock'];
        self::$tableName = $data['table'];
        self::$metadata[$data['table']] = $data['meta'];
        self::$identifier = $data['identifier'];
    }

    public static function extractExt(ClassMetadata $metadata)
    {
        $fields = $metadata->getFieldNames();
        $columns = $metadata->getColumnNames();
        $table = $metadata->getTableName();
        $identifier = null;

        $result = [];
        foreach ($fields as $key => $field) {
            /** @var $columns array */
            foreach ($columns as $key2 => $column) {
                if ($key === $key2) {
                    $result[$table][$field] = $column;
                    if ($field === $metadata->getIdentifier()[0]) {
                        $identifier = $column;
                    }
                }
            }
        }

        $data = [
            'mock' => $result,
            'table' => $table,
            'meta' => $metadata,
            'identifier' => $identifier,
        ];

        return $data;
    }

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

        if (isset($extra['LIMIT']) && \is_array($extra['LIMIT'])) {
            if (isset($extra['LIMIT'][1])) {
                list($offset, $limit) = $extra['LIMIT'];
            } else {
                $offset = 0;
                $limit = $extra['LIMIT'][0];
            }
            $sql = sprintf('%sLIMIT %s, %s', $sql, $offset, $limit);
        }

        return Helper::oneSpace($sql);
    }

    protected static function numeric($tableName, $key, $value)
    {
        $intTypes = [
            'boolean',
            'integer',
            'longint',
        ];
        $flip = [];
        if(isset(self::$mock[$tableName])) {
            $flip = array_flip(self::$mock[$tableName]);
        }

        if (isset(self::$metadata[$tableName], $flip[$key])) {
            if (\in_array(self::$metadata[$tableName]->getFieldMapping($flip[$key])['type'], $intTypes, false)) {
                return true;
            }

            return false;
        }

        return is_numeric($value);
    }

    protected static function buildWhere($tableName, array $where)
    {
        $whereSql = '';
        if (!empty($where)) {
            reset($where);
            $first = key($where);
            $path = ' WHERE ';
            foreach ($where as $key => $value) {
                if (!\is_array($value) && isset(self::$mock[$tableName][$key])) {
                    $whereSql .= $path.self::$mock[$tableName][$key].' = '.(self::numeric($tableName, $key, $value) ? $value : "'".addslashes(trim($value))."'");
                } elseif (\is_array($value)) {
                    $whereSql .= $path.$value[0];
                } else {
                    $whereSql .= $path.$key.' = '.(self::numeric($tableName, $key, $value) ? $value : "'".addslashes(trim($value))."'");
                }
                if ($key === $first) {
                    $path = ' AND ';
                }
            }
        }

        return $whereSql;
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

    protected static function value($object, $variable, $type, $check = true)
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
            Helper::variable($value);
        }

        return $value;
    }
}
