<?php

namespace Sludio\HelperBundle\Script\Repository;

use Sludio\HelperBundle\Script\Utils\Helper;

class QuickInsertRepository
{
    private static $mock = [];
    private static $metadata = [];
    private static $tableName;
    private static $identifier;

    public static $entityManager;
    public static $connection;

    public static function init($noFkCheck = false, $manager = null)
    {
        if (self::$connection) {
            return;
        }
        global $kernel;

        if ('AppCache' === get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        $container = $kernel->getContainer();

        $manager = $manager ?: $container->getParameter('sludio_helper.entity.manager');
        self::$entityManager = $container->get('doctrine')->getManager($manager);
        self::$connection = self::$entityManager->getConnection();

        if (!$noFkCheck) {
            self::runSQL('SET FOREIGN_KEY_CHECKS = 0');
        }
    }

    public static function close($noFkCheck = false)
    {
        if (!$noFkCheck) {
            self::runSQL('SET FOREIGN_KEY_CHECKS = 1');
        }
    }

    private static function extract($object)
    {
        self::init(false);
        $data = self::extractExt($object, self::$entityManager);

        self::$mock = $data['mock'];
        self::$tableName = $data['table'];
        self::$metadata[$data['table']] = $data['meta'];
        self::$identifier = $data['identifier'];
    }

    public static function extractExt($object, $entityManager)
    {
        $metadata = $entityManager->getClassMetadata(get_class($object));

        $fields = $metadata->getFieldNames();
        $columns = $metadata->getColumnNames();
        $table = $metadata->getTableName();
        $identifier = null;

        $result = [];
        foreach ($fields as $key => $field) {
            foreach ($columns as $key2 => $column) {
                if ($key === $key2) {
                    $result[$table][$field] = $column;
                    if($field === $metadata->getIdentifier()[0]){
                        $identifier = $column;
                    }
                }
            }
        }

        $data = [
            'mock' => $result,
            'table' => $table,
            'meta' => $metadata,
            'identifier' => $identifier
        ];

        return $data;
    }

    private static function buildExtra($extra)
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
                if (is_array($extra[$method])) {
                    foreach ($extra[$method] as $group) {
                        $sql .= $group.' ';
                    }
                } else {
                    $sql .= $extra[$method].' ';
                }
            }
        }

        if (isset($extra['LIMIT'])) {
            if (is_array($extra['LIMIT'])) {
                if (isset($extra['LIMIT'][1])) {
                    $offset = $extra['LIMIT'][0];
                    $limit = $extra['LIMIT'][1];
                } else {
                    $offset = 0;
                    $limit = $extra['LIMIT'][0];
                }
                $sql .= 'LIMIT '.$offset.', '.$limit;
            }
        }

        $sql = str_replace('  ', ' ', $sql);

        return $sql;
    }

    private static function buildWhere($tableName, $where)
    {
        $whereSql = '';
        if (is_array($where) && !empty($where)) {
            reset($where);
            $first = key($where);
            $path = ' WHERE ';
            foreach ($where as $key => $value) {
                if (!is_array($value) && isset(self::$mock[$tableName][$key])) {
                    $whereSql .= $path.self::$mock[$tableName][$key]." = ".(is_numeric($value) ? $value : "'".addslashes(trim($value))."'");
                } else {
                    if (is_array($value)) {
                        $whereSql .= $path.$value[0];
                    } else {
                        $whereSql .= $path.$key." = ".(is_numeric($value) ? $value : "'".addslashes(trim($value))."'");
                    }
                }
                if ($key === $first) {
                    $path = ' AND ';
                }
            }
        }

        return $whereSql;
    }

    private static function getTable(&$object, &$tableName, &$columns, &$type, $noFkCheck = true, $manager = null, $extraFields = [])
    {
        self::init($noFkCheck, $manager);
        if (is_object($object)) {
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

    public static function findNextId($tableName)
    {
        $result = self::get(['table_name' => 'information_schema.tables'], true, [
            'table_name' => $tableName,
            ['table_schema = DATABASE()'],
        ], true, ['AUTO_INCREMENT'], null, []);

        if ($result) {
            return $result;
        }

        return 1;
    }

    public static function findNextIdExt($object, $entityManager = null)
    {
        self::init(true);
        $data = self::extractExt($object, $entityManager);

        return self::findNextId($data['table']);
    }

    public static function runSQL($sql, $noFkCheck = true, $manager = null)
    {
        $sql = trim(preg_replace('/\s+/', ' ', $sql));
        self::init($noFkCheck, $manager);
        $sth = self::$connection->prepare($sql);
        $sth->execute();

        self::close($noFkCheck);
        if (substr($sql, 0, 6) === "SELECT") {
            return $sth->fetchAll();
        }
    }

    public static function get($object, $one = false, $where = [], $noFkCheck = true, $fields = [], $manager = null, $extra = [])
    {
        self::getTable($object, $tableName, $columns, $type, $noFkCheck, $manager);

        $select = (isset($extra['MODE']) ? 'SELECT '.$extra['MODE'] : 'SELECT').' ';
        $fields = $fields ?: ['id'];
        $sql = $select.(implode(', ', $fields)).' FROM '.$tableName.self::buildWhere($tableName, $where).self::buildExtra($extra);

        $result = self::runSQL($sql) ?: null;

        if ($result) {
            $field = null;
            if (count($fields) === 1 && $fields[0] !== '*') {
                $field = $fields[0];
            }
            if ($field) {
                if (!$one) {
                    foreach ($result as &$res) {
                        $res = $res[$field];
                    }
                } else {
                    $result = $result[0][$field];
                }
            } elseif ($one) {
                $result = $result[0];
            }
        }

        return $result;
    }

    private static function value($object, $variable, $type, $check = true)
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

    public static function persist($object, $full = false, $extraFields = [], $noFkCheck = false, $manager = null)
    {
        self::getTable($object, $tableName, $columns, $type, $noFkCheck, $manager, $extraFields);

        $id = self::findNextId($tableName);
        $data = [];

        $idd = null;
        foreach ($columns as $value => $key) {
            if (!is_array($key) && !is_array($value)) {
                $value = self::value($object, $value, $type);
                if ($value !== null) {
                    $data[$key] = $value;
                    if ($key === self::$identifier) {
                        $idd = $value;
                    }
                }
            }
        }

        if (!$full) {
            $data[self::$identifier] = $id;
        } else {
            $id = $idd;
        }

        if (Helper::isEmpty($data) && $id !== null) {
            return null;
        }

        $sql = '
            INSERT INTO
                '.$tableName.'
                    ('.implode(',', array_keys($data)).')
            VALUES
                ('.implode(',', array_values($data)).')
        ';

        self::runSQL($sql);

        return $id;
    }

    public static function update($id, $object, $extraFields = [], $noFkCheck = false, $manager = null)
    {
        self::getTable($object, $tableName, $columns, $type, $noFkCheck, $manager, $extraFields);

        $result = self::get(['table_name' => $tableName], true, ['id' => $id], true, ['*']);
        unset($result['id']);
        $data = [];

        $flip = array_flip($columns);
        foreach ($result as $key => $value) {
            $content = self::value($object, $key, $type, false);
            if ($content !== $value) {
                $data[$key] = $content;
            }
            if (!$id && $content === null) {
                unset($data[$key]);
            }
        }

        if ($data) {
            $sql = '
                UPDATE
                    '.$tableName.'
                SET

            ';
            foreach ($data as $key => $value) {
                $meta = self::$metadata[$tableName]->getFieldMapping($flip[$key])['type'];
                if (in_array($meta, [
                    'boolean',
                    'integer',
                    'longint',
                ])) {
                    $value = intval($value);
                } else {
                    $value = "'".addslashes(trim($value))."'";
                }
                $sql .= " ".$key." = ".$value.",";
            }
            $sql = substr($sql, 0, -1).' WHERE id = '.$id;

            self::runSQL($sql);
        }
    }

    public static function delete($object, $where = [], $noFkCheck = false, $manager = null)
    {
        self::getTable($object, $tableName, $columns, $type, $noFkCheck, $manager);

        $sql = 'DELETE FROM '.$tableName.self::buildWhere($tableName, $where);
        self::runSQL($sql);
    }

    public static function link($object, $data, $noFkCheck = false, $manager = null)
    {
        self::getTable($object, $tableName, $columns, $type, $noFkCheck, $manager);

        $data['table_name'] = $tableName;
        self::persist($data, true, [], $noFkCheck, $manager);
    }
}
