<?php

namespace Sludio\HelperBundle\Script\Repository;

use DateTime;

class QuickInsertRepository
{
    private static $mock = array();
    private static $metadata = array();
    private static $tableName;

    public static $em;
    public static $connection;
    public static $container;

    public static function init($no_fk_check = false, $manager = null)
    {
        global $kernel;

        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        self::$container = $kernel->getContainer();

        $manager = $manager ?: self::$container->getParameter('sludio_helper.entity.manager');
        self::$em = self::$container->get('doctrine')->getManager($manager);
        self::$connection = self::$em->getConnection();

        if(!$no_fk_check) {
            $sth = self::$connection->prepare('SET FOREIGN_KEY_CHECKS = 0');
            $sth->execute();
        }
    }

    public static function close($no_fk_check = false)
    {
        if(!$no_fk_check) {
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

    public static function persist($object, $full = false, $extra = array(), $no_fk_check = false, $manager = null)
    {
        self::init($no_fk_check, $manager);
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
                if ($object->{'get'.ucfirst($value)}() instanceof DateTime) {
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

        self::close($no_fk_check);
        return $id;
    }
    
    private static function buildExtra($tableName, $extra)
    {
        $methods = array(
            'GROUP BY',
            'HAVING',
            'ORDER BY',
        );
        $sql = '';
        
        foreach($methods as $method){
            if(isset($extra[$method])){
                $sql .= $method.' ';
                if(is_array($extra[$method])){
                    foreach($extra[$method] as $group){
                        $sql .= $group.' ';
                    }
                } else {
                    $sql .= $extra[$method].' ';
                }
            }
        }
        
        if(isset($extra['LIMIT'])){
            if(is_array($extra['LIMIT'])){
                if(isset($extra['LIMIT'][1])){
                    $offset = $extra['LIMIT'][0];
                    $limit = $extra['LIMIT'][1];
                } else {
                    $offset = 0;
                    $limit = $extra['LIMIT'][0];
                }
                $sql .= 'LIMIT '.$offset.', '.$limit;
            }
        }
        
        return $sql;
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

    public static function get($object, $one = false, $where = array(), $no_fk_check = false, $fields = array(), $manager = null, $extra = array())
    {
        self::init($no_fk_check, $manager);
        self::extract($object);
        $whereSql = self::buildWhere(self::$tableName, $where);
        $select = (isset($extra['MODE']) ? 'SELECT '.$extra['MODE'] : 'SELECT').' ';
        if(!$fields){
            $sql = $select.'id FROM '.self::$tableName.' '.$whereSql;
        } else {
            $sql = $select.(implode(', ', $fields)).' FROM '.self::$tableName.' '.$whereSql;
        }
        if(!empty($extra)){
            $extraSql = self::buildExtra(self::$tableName, $extra);
            $sql .= $extraSql;
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

        self::close($no_fk_check);
        if($one) {
            return null;
        }
        return $result;
    }

    public static function link($object, $data, $no_fk_check = false, $manager = null)
    {
        self::init($no_fk_check, $manager);
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

        self::close($no_fk_check);
    }

    public static function linkTable($tableName, $data, $no_fk_check = false, $manager = null)
    {
        self::init($no_fk_check, $manager);
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

        self::close($no_fk_check);
    }

    public static function update($id, $object, $extra = array(), $no_fk_check = false, $manager = null)
    {
        self::init($no_fk_check, $manager);
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

        self::close($no_fk_check);
    }

    public static function delete($object, $where = array(), $no_fk_check = false, $manager = null)
    {
        self::init($no_fk_check, $manager);
        self::extract($object);
        $whereSql = self::buildWhere(self::$tableName, $where);
        $sql = 'DELETE FROM '.self::$tableName.' '.$whereSql;
        $sth = self::$connection->prepare($sql);
        $sth->execute();

        self::close($no_fk_check);
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
