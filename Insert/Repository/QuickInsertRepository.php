<?php

namespace Sludio\HelperBundle\Insert\Repository;

use Sludio\HelperBundle\Usort\Repository\UsortRepository;

class QuickInsertRepository extends UsortRepository
{
    private static $mock = array();
    private static $tableName;
    
    private static function init()
    {
        parent::init();
        $sth = self::$connection->prepare('SET FOREIGN_KEY_CHECKS = 0');
        $sth->execute();
    }
    
    private static function close()
    {
        $sth = self::$connection->prepare('SET FOREIGN_KEY_CHECKS = 1');
        $sth->execute();
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
    }
    
    public static function persist($object, $full = false)
    {
        self::init();
        self::extract($object);
        $id = self::findNextId($object);
        $keys = array();
        $values = array();
        foreach (self::$mock[self::$tableName] as $value => $key) {
            $vvv = null;
            if ($object->{'get'.ucfirst($value)}() instanceof \DateTime) {
                $vvv = "'".addslashes(trim($object->{'get'.ucfirst($value)}()->format('Y-m-d H:i:s')))."'";
            } else {
                $vvv = "'".addslashes(trim($object->{'get'.ucfirst($value)}()))."'";
            }
            if (trim($vvv) == '' || trim($vvv) == "''" || (is_numeric($vvv) && $vvv === 0)) {
                $vvv = null;
            }
            if ($vvv) {
                $values[] = $vvv;
                $keys[] = $key;
                if ($key == 'id') {
                    $idd = $object->{'get'.ucfirst($value)}();
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
            $sth = self::$connection->prepare('SET FOREIGN_KEY_CHECKS = 1');
            $sth->execute();
        }

        self::close();
        return $id;
    }
    
    private static function buildWhere($tableName, $where)
    {
        $whereSql = '';
        if ($where) {
            foreach ($where as $key => $value) {
                $fk = $key;
                $f = $value;
                break;
            }
            $whereSql .= ' WHERE '.self::$mock[$tableName][$fk]." = '".$f."'";
            unset($where[$fk]);
            if ($where) {
                foreach ($where as $key => $value) {
                    $whereSql .= ' AND '.self::$mock[$tableName][$key]." = '".$value."'";
                }
            }
        }
        
        return $whereSql;
    }

    public static function get($object, $one = false, $where = array())
    {
        self::init();
        self::extract($object);
        $whereSql = self::buildWhere(self::$tableName, $where);
        $sql = 'SELECT id FROM '.self::$tableName.' '.$whereSql;
        $sth = self::$connection->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll();
        if ($one && $result) {
            $result = intval($result[0]['id']);
        }

        self::close();
        return $result;
    }

    public static function link($object, $data)
    {
        self::init();
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
        
        self::close();
    }
    
    public static function update($id, $object)
    {
        self::init();
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
        $flip = array_flip(self::$mock[self::$tableName]);
        foreach ($result as $key => $value) {
            if (trim($value) == '' && trim($object->{'get'.ucfirst($flip[$key])}()) != '') {
                $data[self::$mock[self::$tableName][$flip[$key]]] = $object->{'get'.ucfirst($flip[$key])}();
            }
        }
        if ($data) {
            $sqlu = "
                UPDATE
                    ".self::$tableName."
                SET
                    
            ";
            foreach ($data as $key => $value) {
                $sqlu .= " ".$key." = '".$value."',";
            }
            $sqlu = substr($sqlu, 0, -1);
            $sqlu .= " WHERE id = ".$id;
            $sthu = self::$connection->prepare($sqlu);
            $sthu->execute();
        }
        
        self::close();
    }
    
    public static function delete($object, $where = array())
    {
        self::init();
        self::extract($object);
        $whereSql = self::buildWhere(self::$tableName, $where);
        $sql = 'DELETE FROM '.self::$tableName.' '.$whereSql;
        $sth = self::$connection->prepare($sql);
        $sth->execute();
        
        self::close();
    }

    public static function findNextId($object)
    {
        self::init();
        self::extract($object);
        $sql = "
            SHOW 
                TABLE STATUS 
            LIKE 
                '".self::$tableName."'
        ";
        $sth = self::$connection->prepare($sql);
        $sth->execute();
        $result = $sth->fetch();

        if (isset($result['Auto_increment'])) {
            return (int) $result['Auto_increment'];
        }

        slef::close();
        return 1;
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
}
