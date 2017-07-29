<?php

namespace Sludio\HelperBundle\Script\Repository;

class QuickInsertRepository
{
    private static $mock = array();
    private static $metadata = array();
    private static $tableName;

    public static $em;
    public static $connection;
    public static $container;

    public static function init($dont = false, $manager = null)
    {
        global $kernel;

        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        self::$container = $kernel->getContainer();

        $manager = $manager ?: self::$container->getParameter('sludio_helper.entity.manager');
        self::$em = self::$container->get('doctrine')->getManager($manager);
        self::$connection = self::$em->getConnection();

        if(!$dont) {
            $sth = self::$connection->prepare('SET FOREIGN_KEY_CHECKS = 0');
            $sth->execute();
        }
    }

    public static function close($dont = false)
    {
        if(!$dont) {
            $sth = self::$connection->prepare('SET FOREIGN_KEY_CHECKS = 1');
            $sth->execute();
        }
    }

    private static function extract($object)
    {
        $metadata = self::$em->getClassMetadata(get_class($object));

        $fields = $metadata->getFieldNames();
        $columns = $metadata->getColumnNames();
        $table = $metadata->getTableName();

        $result = array();
        foreach ($fields as $key => $field) {
            foreach ($columns as $key2 => $column) {
                if ($key === $key2) {
                    $result[$table][$field] = $column;
                }
            }
        }

        self::$mock = $result;
        self::$tableName = $table;
        self::$metadata[$table] = $metadata;
    }

    public static function extractExt($object, $em)
    {
        $metadata = $em->getClassMetadata(get_class($object));

        $fields = $metadata->getFieldNames();
        $columns = $metadata->getColumnNames();
        $table = $metadata->getTableName();

        $result = array();
        foreach ($fields as $key => $field) {
            foreach ($columns as $key2 => $column) {
                if ($key === $key2) {
                    $result[$table][$field] = $column;
                }
            }
        }

        $data = array(
            'mock' => $result,
            'table' => $table,
            'meta' => $metadata
        );

        return $data;
    }

    public static function persist($object, $full = false, $extra = array(), $dont = false, $manager = null)
    {
        self::init($dont, $manager);
        self::extract($object);
        $id = self::findNextId($object);
        $keys = array();
        $values = array();

        $columns = self::$mock[self::$tableName];
        if(!empty($extra) && isset($extra[self::$tableName])) {
            $columns = array_merge(self::$mock[self::$tableName], $extra[self::$tableName]);
        }

        foreach ($columns as $value => $key) {
            $variable = null;
            if(!is_array($key) && !is_array($value)) {
                if ($object->{'get'.ucfirst($value)}() instanceof \DateTime) {
                    $variable = "'".addslashes(trim($object->{'get'.ucfirst($value)}()->format('Y-m-d H:i:s')))."'";
                } else {
                    $variable = "'".addslashes(trim($object->{'get'.ucfirst($value)}()))."'";
                }
                if (trim($variable) === '' || trim($variable) === "''" || (is_numeric($variable) && $variable === 0)) {
                    $variable = null;
                }
                if ($variable) {
                    $values[] = $variable;
                    $keys[] = $key;
                    if ($key === 'id') {
                        $idd = $object->{'get'.ucfirst($value)}();
                    }
                }
            }
        }
        $sql = null;
        if (!$full && !self::isEmpty($values)) {
            $sql = '
                INSERT INTO
                    '.self::$tableName.'
                        (id, '.implode(',', $keys).")
                VALUES
                    ({$id},".implode(',', $values).')
            ';
        } elseif ($full && !self::isEmpty($values)) {
            $id = $idd;
            $sql = '
                INSERT INTO
                    '.self::$tableName.'
                        ('.implode(',', $keys).")
                VALUES
                    (".implode(',', $values).')
            ';
        } else {
            $id = null;
        }
        if ($sql && $id) {
            $sth = self::$connection->prepare($sql);
            $sth->execute();
        }

        self::close($dont);
        return $id;
    }

    private static function buildWhere($tableName, $where)
    {
        $whereSql = '';
        if ($where) {
            foreach ($where as $key => $value) {
                $fk = $key;
                $f = trim($value);
                break;
            }
            if(isset(self::$mock[$tableName][$fk])) {
                if(is_numeric($f)){
                    $whereSql .= ' WHERE '.self::$mock[$tableName][$fk]." = $f";
                } else {
                    $whereSql .= ' WHERE '.self::$mock[$tableName][$fk]." = '".addslashes(trim($f))."'";
                }
            } else {
                if(is_numeric($f)){
                    $whereSql .= ' WHERE '.$fk." = $f";
                } else {
                    $whereSql .= ' WHERE '.$fk." = '".addslashes(trim($f))."'";
                }
            }
            unset($where[$fk]);
            if ($where) {
                foreach ($where as $key => $value) {
                    if(isset(self::$mock[$tableName][$key])) {
                        if(is_numeric($value)){
                            $whereSql .= ' AND '.self::$mock[$tableName][$key]." = $value";
                        } else {
                            $whereSql .= ' AND '.self::$mock[$tableName][$key]." = '".addslashes(trim($value))."'";
                        }
                    } else {
                        if(is_numeric($value)){
                            $whereSql .= ' AND '.$key." = $value";
                        } else {
                            $whereSql .= ' AND '.$key." = '".addslashes(trim($value))."'";
                        }
                    }
                }
            }
        }

        return $whereSql;
    }

    public static function get($object, $one = false, $where = array(), $dont = false, $fields = array(), $manager = null)
    {
        self::init($dont, $manager);
        self::extract($object);
        $whereSql = self::buildWhere(self::$tableName, $where);
        if(!$fields){
            $sql = 'SELECT id FROM '.self::$tableName.' '.$whereSql;
        } else {
            $sql = 'SELECT '.(implode(', ', $fields)).' FROM '.self::$tableName.' '.$whereSql;
        }
        $sth = self::$connection->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll();
        if ($one && $result) {
            if(!$fields){
                return intval($result[0]['id']);
            } else {
                if(count($fields) === 1){
                    return $result[0][$fields[0]];
                } else {
                    return $result[0];
                }
            }
        }

        self::close($dont);
        if($one) {
            return null;
        }
        return $result;
    }

    public static function link($object, $data, $dont = false, $manager = null)
    {
        self::init($dont, $manager);
        self::extract($object);
        if ($object && $data) {
            $keys = $values = array();
            foreach ($data as $key => $value) {
                $keys[] = $key;
                $values[] = $value;
            }
            $sql = "
                INSERT IGNORE INTO
                    ".self::$tableName."
                        (".implode(',', $keys).")
                VALUES
                    (".implode(',', $values).")
            ";
            $sth = self::$connection->prepare($sql);
            $sth->execute();
        }

        self::close($dont);
    }

    public static function linkTable($tableName, $data, $dont = false, $manager = null)
    {
        self::init($dont, $manager);
        if ($data) {
            $keys = $values = array();
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
            $sth = self::$connection->prepare($sql);
            $sth->execute();
        }

        self::close($dont);
    }

    public static function update($id, $object, $extra = array(), $dont = false, $manager = null)
    {
        self::init($dont, $manager);
        self::extract($object);
        $sqls = "
            SELECT
                *
            FROM
                ".self::$tableName."
            WHERE
                id = ".$id
        ;
        $sths = self::$connection->prepare($sqls);
        $sths->execute();
        $result = $sths->fetchAll();
        if ($result && isset($result[0])) {
            $result = $result[0];
        }
        unset($result['id']);
        $data = array();

        $columns = self::$mock[self::$tableName];
        if(!empty($extra) && isset($extra[self::$tableName])) {
            $columns = array_merge(self::$mock[self::$tableName], $extra[self::$tableName]);
        }

        $flip = array_flip($columns);
        foreach ($result as $key => $value) {
            $data[self::$mock[self::$tableName][$flip[$key]]] = $object->{'get'.ucfirst($flip[$key])}();
        }

        if ($data) {
            $sqlu = "
                UPDATE
                    ".self::$tableName."
                SET

            ";
            foreach ($data as $key => $value) {
                $meta = self::$metadata[self::$tableName]->getFieldMapping($flip[$key]);
                $meta = $meta['type'];
                if(in_array($meta, ['boolean','integer','longint'])){
                    $value = intval($value);
                } else {
                    $value = "'".addslashes(trim($value))."'";
                }
                $sqlu .= " ".$key." = ".$value.",";
            }
            $sqlu = substr($sqlu, 0, -1);
            $sqlu .= " WHERE id = ".$id;
            $sthu = self::$connection->prepare($sqlu);
            $sthu->execute();
        }

        self::close($dont);
    }

    public static function delete($object, $where = array(), $dont = false, $manager = null)
    {
        self::init($dont, $manager);
        self::extract($object);
        $whereSql = self::buildWhere(self::$tableName, $where);
        $sql = 'DELETE FROM '.self::$tableName.' '.$whereSql;
        $sth = self::$connection->prepare($sql);
        $sth->execute();

        self::close($dont);
    }

    public static function isEmpty($variable)
    {
        $result = true;

        if (is_array($variable) && count($variable) > 0) {
            foreach ($variable as $Value) {
                $result = $result && self::isEmpty($Value);
            }
        } else {
            $result = empty($variable);
        }

        return $result;
    }

    public static function findNextId($object)
    {
        self::extract($object);
        $sql = "
            SELECT
                AUTO_INCREMENT
            FROM
                information_schema.tables
            WHERE
                table_name = '".self::$tableName."'
            AND
                table_schema = DATABASE()
        ";
        $sth = self::$connection->prepare($sql);
        $sth->execute();
        $result = $sth->fetch();

        if (isset($result['AUTO_INCREMENT'])) {
            return (int) $result['AUTO_INCREMENT'];
        }

        return 1;
    }
}
