<?php

namespace Sludio\HelperBundle\Script\Repository;

class QuickInsertRepository
{
    private static $mock = [];
    private static $metadata = [];
    private static $tableName;

    public static $entityManager;
    public static $connection;
    public static $container;

    public static function init($noFkCheck = false, $manager = null)
    {
        global $kernel;

        if ('AppCache' === get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        self::$container = $kernel->getContainer();

        $manager = $manager ?: self::$container->getParameter('sludio_helper.entity.manager');
        self::$entityManager = self::$container->get('doctrine')->getManager($manager);
        self::$connection = self::$entityManager->getConnection();

        if (!$noFkCheck) {
            $sth = self::$connection->prepare('SET FOREIGN_KEY_CHECKS = 0');
            $sth->execute();
        }
    }

    public static function close($noFkCheck = false)
    {
        if (!$noFkCheck) {
            $sth = self::$connection->prepare('SET FOREIGN_KEY_CHECKS = 1');
            $sth->execute();
        }
    }

    public static function isEmpty($variable)
    {
        $result = true;

        if (is_array($variable) && count($variable) > 0) {
            foreach ($variable as $value) {
                $result = $result && self::isEmpty($value);
            }
        } else {
            $result = empty($variable);
        }

        return $result;
    }

    private static function extract($object)
    {
        self::init(false);
        $data = self::extractExt($object, self::$entityManager);

        self::$mock = $data['mock'];
        self::$tableName = $data['table'];
        self::$metadata[$data['table']] = $data['meta'];
    }

    public static function extractExt($object, $entityManager)
    {
        $metadata = $entityManager->getClassMetadata(get_class($object));

        $fields = $metadata->getFieldNames();
        $columns = $metadata->getColumnNames();
        $table = $metadata->getTableName();

        $result = [];
        foreach ($fields as $key => $field) {
            foreach ($columns as $key2 => $column) {
                if ($key === $key2) {
                    $result[$table][$field] = $column;
                }
            }
        }

        $data = [
            'mock' => $result,
            'table' => $table,
            'meta' => $metadata,
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
        $whereSql = $fvalue = $fkey = '';
        if ($where && is_array($where)) {
            $skip = false;
            foreach ($where as $key => $value) {
                $fkey = $key;
                if (is_array($value)) {
                    $skip = true;
                    $fvalue = trim($value[0]);
                } else {
                    $fvalue = trim($value);
                }
                break;
            }
            if (!$skip && isset(self::$mock[$tableName][$fkey])) {
                if (is_numeric($fvalue)) {
                    $whereSql .= ' WHERE '.self::$mock[$tableName][$fkey]." = $fvalue";
                } else {
                    $whereSql .= ' WHERE '.self::$mock[$tableName][$fkey]." = '".addslashes(trim($fvalue))."'";
                }
            } else {
                if (!$skip && is_numeric($fvalue)) {
                    $whereSql .= ' WHERE '.$fkey." = $fvalue";
                } elseif (!$skip && !is_numeric($fvalue)) {
                    $whereSql .= ' WHERE '.$fkey." = '".addslashes(trim($fvalue))."'";
                } elseif ($skip && is_numeric($fkey)) {
                    $whereSql .= " WHERE $fvalue";
                }
            }
            unset($where[$fkey]);
            if ($where && is_array($where)) {
                foreach ($where as $key => $value) {
                    $skip = is_array($value);
                    if (!$skip && isset(self::$mock[$tableName][$key])) {
                        if (is_numeric($value)) {
                            $whereSql .= ' AND '.self::$mock[$tableName][$key]." = $value";
                        } else {
                            $whereSql .= ' AND '.self::$mock[$tableName][$key]." = '".addslashes(trim($value))."'";
                        }
                    } else {
                        if (!$skip && is_numeric($value)) {
                            $whereSql .= ' AND '.$key." = $value";
                        } elseif (!$skip && !is_numeric($value)) {
                            $whereSql .= ' AND '.$key." = '".addslashes(trim($value))."'";
                        } elseif ($skip && is_numeric($key)) {
                            $whereSql .= " AND {$value[0]}";
                        }
                    }
                }
            }
        }

        return $whereSql;
    }

    public static function findNextId($tableName, &$out = null)
    {
        $sql = "
            SELECT
                AUTO_INCREMENT
            FROM
                information_schema.tables
            WHERE
                table_name = '".$tableName."'
            AND
                table_schema = DATABASE()
        ";
        if ($out) {
            $out = $sql;
        }
        $sth = self::$connection->prepare($sql);
        $sth->execute();
        $result = $sth->fetch();

        if (isset($result['AUTO_INCREMENT'])) {
            return (int)$result['AUTO_INCREMENT'];
        }

        return 1;
    }

    public static function findNextIdExt($object, $entityManager, &$out = null)
    {
        self::init(true);
        $data = self::extractExt($object, $entityManager);

        return self::findNextId($data['table'], $out);
    }

    public static function get($object, $one = false, $where = [], $noFkCheck = true, $fields = [], $manager = null, $extra = [], &$out = null)
    {
        self::init($noFkCheck, $manager);
        self::getTable($object, $tableName, $columns, $type);

        $whereSql = self::buildWhere($tableName, $where);
        $select = (isset($extra['MODE']) ? 'SELECT '.$extra['MODE'] : 'SELECT').' ';
        if (!$fields) {
            $sql = $select.'id FROM '.$tableName.' '.$whereSql;
        } else {
            $sql = $select.(implode(', ', $fields)).' FROM '.$tableName.' '.$whereSql;
        }
        if (!empty($extra)) {
            $extraSql = self::buildExtra($extra);
            $sql .= $extraSql;
        }
        if ($out) {
            $out = $sql;
        }
        $sth = self::$connection->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll();
        if ($one && $result) {
            if (!$fields) {
                return intval($result[0]['id']);
            } else {
                if (count($fields) === 1 && $fields[0] !== '*') {
                    return $result[0][$fields[0]];
                } else {
                    return $result[0];
                }
            }
        }

        self::close($noFkCheck);
        if ($one || !$result) {
            return null;
        }

        $field = null;
        if (!$fields) {
            $field = 'id';
        } elseif (count($fields) === 1 && $fields[0] !== '*') {
            $field = $fields[0];
        }

        if ($field) {
            foreach ($result as &$res) {
                $res = $res[$field];
            }
        }

        return $result;
    }

    private static function getTable(&$object, &$tableName, &$columns, &$type)
    {
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
    }

    public static function persist($object, $full = false, $extraFields = [], $noFkCheck = false, $manager = null, &$out = null)
    {
        self::init($noFkCheck, $manager);
        self::getTable($object, $tableName, $columns, $type);

        $id = self::findNextId($tableName);
        $keys = [];
        $values = [];

        if (!empty($extraFields) && isset($extraFields[$tableName])) {
            $columns = array_merge($columns, $extraFields[$tableName]);
        }

        if ($type === 'object') {
            foreach ($columns as $value => $key) {
                $variable = null;
                if (!is_array($key) && !is_array($value)) {
                    if ($object->{'get'.ucfirst($value)}() instanceof \DateTime) {
                        $variable = "'".addslashes(trim($object->{'get'.ucfirst($value)}()->format('Y-m-d H:i:s')))."'";
                    } else {
                        $variable = "'".addslashes(trim($object->{'get'.ucfirst($value)}()))."'";
                    }
                    if (trim($variable) === '' || trim($variable) === "''" || (is_numeric($variable) && $variable === 0)) {
                        $variable = null;
                    }
                    if ($variable !== null) {
                        $values[] = $variable;
                        $keys[] = $key;
                        if ($key === 'id') {
                            $idd = $object->{'get'.ucfirst($value)}();
                        }
                    }
                }
            }
        } else {
            foreach ($columns as $value => $key) {
                $variable = null;
                if (!is_array($key) && !is_array($value) && isset($object[$value])) {
                    if ($object[$value] instanceof \DateTime) {
                        $variable = "'".addslashes(trim($object[$value]->format('Y-m-d H:i:s')))."'";
                    } else {
                        $variable = "'".addslashes(trim($object[$value]))."'";
                    }
                    if (trim($variable) === '' || trim($variable) === "''" || (is_numeric($variable) && $variable === 0)) {
                        $variable = null;
                    }
                    if ($variable !== null) {
                        $values[] = $variable;
                        $keys[] = $key;
                        if ($key === 'id') {
                            $idd = $object[$value];
                        }
                    }
                }
            }
        }

        $sql = null;
        if (!$full && !self::isEmpty($values)) {
            $sql = '
                INSERT INTO
                    '.$tableName.'
                        (id, '.implode(',', $keys).")
                VALUES
                    ({$id},".implode(',', $values).')
            ';
        } elseif ($full && !self::isEmpty($values)) {
            $id = $idd;
            $sql = '
                INSERT INTO
                    '.$tableName.'
                        ('.implode(',', $keys).")
                VALUES
                    (".implode(',', $values).')
            ';
        } else {
            $id = null;
        }
        if ($sql !== null && $id) {
            if ($out) {
                $out = $sql;
            }
            $sth = self::$connection->prepare($sql);
            $sth->execute();
        }

        self::close($noFkCheck);

        return $id;
    }

    public static function update($id, $object, $extraFields = [], $noFkCheck = false, $manager = null, &$out = null)
    {
        self::init($noFkCheck, $manager);
        self::getTable($object, $tableName, $columns, $type);

        $result = self::get($tableName, true, ['id' => $id], true, ['*']);
        unset($result['id']);

        $data = [];

        if (!empty($extraFields) && isset($extraFields[$tableName])) {
            $columns = array_merge($columns, $extraFields[$tableName]);
        }

        $flip = array_flip($columns);
        if ($type === 'object') {
            if ($id) {
                foreach ($result as $key => $value) {
                    if ($object->{'get'.ucfirst($flip[$key])}() !== $value) {
                        $data[$columns[$flip[$key]]] = $object->{'get'.ucfirst($flip[$key])}();
                    }
                }
            } else {
                foreach ($result as $key => $value) {
                    if ($object->{'get'.ucfirst($flip[$key])}() !== null) {
                        if ($object->{'get'.ucfirst($flip[$key])}() !== $value) {
                            $data[$columns[$flip[$key]]] = $object->{'get'.ucfirst($flip[$key])}();
                        }
                    }
                }
            }
        } else {
            foreach ($result as $key => $value) {
                if (isset($object[$key]) && $object[$key] !== $value) {
                    $data[$key] = $extraFields[$key];
                }
            }

        }

        if ($data) {
            $sql = "
                UPDATE
                    ".$tableName."
                SET

            ";
            foreach ($data as $key => $value) {
                $meta = self::$metadata[$tableName]->getFieldMapping($flip[$key]);
                $meta = $meta['type'];
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
            $sql = substr($sql, 0, -1);
            $sql .= " WHERE id = ".$id;
            if ($out) {
                $out = $sql;
            }

            $sthu = self::$connection->prepare($sql);
            $sthu->execute();
        }

        self::close($noFkCheck);
    }

    public static function delete($object, $where = [], $noFkCheck = false, $manager = null, &$out = null)
    {
        self::init($noFkCheck, $manager);
        self::getTable($object, $tableName, $columns, $type);

        $whereSql = self::buildWhere($tableName, $where);
        $sql = 'DELETE FROM '.$tableName.' '.$whereSql;
        if ($out) {
            $out = $sql;
        }
        $sth = self::$connection->prepare($sql);
        $sth->execute();

        self::close($noFkCheck);
    }

    public static function link($object, $data, $noFkCheck = false, $manager = null, &$out = null)
    {
        self::init($noFkCheck, $manager);
        self::getTable($object, $tableName, $columns, $type);

        if ($object && $data) {
            $keys = $values = [];
            foreach ($data as $key => $value) {
                $keys[] = $key;
                $values[] = $value;
            }
            $sql = "
                INSERT IGNORE INTO
                    ".$tableName."
                        (".implode(',', $keys).")
                VALUES
                    (".implode(',', $values).")
            ";
            if ($out) {
                $out = $sql;
            }
            $sth = self::$connection->prepare($sql);
            $sth->execute();
        }

        self::close($noFkCheck);
    }
}
