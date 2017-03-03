<?php

namespace Sludio\HelperBundle\Insert\Repository;

class QuickInsertRepository
{
    private static $mock = array();
    private static $tableName;
    
    public static $em;
    public static $connection;
    public static $container;
    public static $manager;
    
    public static function init($manager = 'mysql', $dont = false)
    {
        switch($manager){
            case 'mysql': self::initMysql($dont); break;
            case 'oracle': self::initOracle(); break;
        }
    }
    
    public static function initMysql($dont = false)
    {
        global $kernel;

        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        self::$container = $kernel->getContainer();

        self::$em = self::$container->get('doctrine')->getManager(self::$container->getParameter('sludio_helper.entity.manager'));
        self::$connection = self::$em->getConnection();
        
        if(!$dont){
            $sth = self::$connection->prepare('SET FOREIGN_KEY_CHECKS = 0');
            $sth->execute();
        }
    }
    
    public static function initOracle()
    {
        global $kernel;

        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        self::$container = $kernel->getContainer();

        self::$em = self::$container->get('doctrine')->getManager(self::$container->getParameter('sludio_helper.entity.oracle_manager'));
        self::$connection = self::$em->getConnection();
    }
    
    public static function close($manager = 'mysql', $dont = false)
    {
        if(!$dont && self::$manager == 'mysql'){
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
    }
    
    public static function persistMysql($object, $full = false, $extra = array(), $dont = false){
        return self::persist('mysql', $object, $full, $extra, $dont);
    }
    
    public static function persistOracle($object, $full = false, $extra = array(), $dont = false){
        return self::persist('oracle', $object, $full, $extra, $dont);
    }
    
    public static function persist($manager = 'mysql', $object, $full = false, $extra = array(), $dont = false)
    {
        self::init($manager, $dont);
        self::extract($object);
        $id = self::findNextId($object);
        $keys = array();
        $values = array();
        
        $columns = self::$mock[self::$tableName];
        if(!empty($extra) && isset($extra[self::$tableName])){
            $columns = array_merge(self::$mock[self::$tableName], $extra[self::$tableName]);
        }
        
        foreach ($columns as $value => $key) {
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
        }

        self::close($manager, $dont);
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
            if(isset(self::$mock[$tableName][$fk])){
                $whereSql .= ' WHERE '.self::$mock[$tableName][$fk]." = '".$f."'";
            } else {
                $whereSql .= ' WHERE '.$fk." = '".$f."'";
            }
            unset($where[$fk]);
            if ($where) {
                foreach ($where as $key => $value) {
                    if(isset(self::$mock[$tableName][$key])){
                        $whereSql .= ' AND '.self::$mock[$tableName][$key]." = '".$value."'";
                    } else {
                        $whereSql .= ' AND '.$key." = '".$value."'";
                    }
                }
            }
        }
        
        return $whereSql;
    }
    
    private function buildWhereExtended($tableName, $where){
        $whereSql = '';
        if ($where) {
            
        }
    }
    
    public static function getMysql($object, $one = false, $where = array(), $dont = false){
        return self::get('mysql', $object, $one, $where, $dont);
    }
    
    public static function getOracle($object, $one = false, $where = array(), $dont = false){
        return self::get('oracle', $object, $one, $where, $dont);
    }

    public static function get($manager = 'mysql', $object, $one = false, $where = array(), $dont = false)
    {
        self::init($manager, $dont);
        self::extract($object);
        $whereSql = self::buildWhere(self::$tableName, $where);
        $sql = 'SELECT id FROM '.self::$tableName.' '.$whereSql;
        $sth = self::$connection->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll();
        if ($one && $result) {
            return intval($result[0]['id']);
        }

        self::close($manager, $dont);
        if($one){
            return null;
        }
        return $result;
    }
    
    public static function linkMysql($object, $data, $dont = false){
        return self::link('mysql', $object, $data, $dont);
    }
    
    public static function linkOracle($object, $data, $dont = false){
        return self::link('oracle', $object, $data, $dont);
    }

    public static function link($manager = 'mysql', $object, $data, $dont = false)
    {
        self::init($manager, $dont);
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
        
        self::close($manager, $dont);
    }
    
    public static function linkTableMysql($tableName, $data, $dont = false){
        return self::link('mysql', $tableName, $data, $dont);
    }
    
    public static function linkTableOracle($tableName, $data, $dont = false){
        return self::link('oracle', $tableName, $data, $dont);
    }
    
    public static function linkTable($manager = 'mysql', $tableName, $data, $dont = false)
    {
        self::init($manager, $dont);
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
        
        self::close($manager, $dont);
    }
    
    public static function updateMysql($id, $object, $extra = array(), $dont = false){
        return self::update('mysql', $id, $object, $extra, $dont);
    }
    
    public static function updateOracle($id, $object, $extra = array(), $dont = false){
        return self::update('oracle', $id, $object, $extra, $dont);
    }
    
    public static function update($manager = 'mysql', $id, $object, $extra = array(), $dont = false)
    {
        self::init($manager, $dont);
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
        if(!empty($extra) && isset($extra[self::$tableName])){
            $columns = array_merge(self::$mock[self::$tableName], $extra[self::$tableName]);
        }
        
        $flip = array_flip($columns);
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
        
        self::close($manager, $dont);
    }
    
    public static function deleteMysql($object, $where = array(), $dont = false){
        return self::delete('mysql', $object, $where, $dont);
    }
    
    public static function deleteOracle($object, $where = array(), $dont = false){
        return self::delete('oracle', $object, $where, $dont);
    }
    
    public static function delete($manager = 'mysql', $object, $where = array(), $dont = false)
    {
        self::init($manager, $dont);
        self::extract($object);
        $whereSql = self::buildWhere(self::$tableName, $where);
        $sql = 'DELETE FROM '.self::$tableName.' '.$whereSql;
        $sth = self::$connection->prepare($sql);
        $sth->execute();
        
        self::close($manager, $dont);
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
    
    public static function findNextId2($object)
    {
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

        return 1;
    }
    
    public static function findNextId3($object)
    {
        self::extract($object);
        $sql = "
            SHOW 
                TABLE STATUS 
            WHERE 
                name = '".self::$tableName."'
        ";
        $sth = self::$connection->prepare($sql);
        $sth->execute();
        $result = $sth->fetch();

        if (isset($result['Auto_increment'])) {
            return (int) $result['Auto_increment'];
        }

        return 1;
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
