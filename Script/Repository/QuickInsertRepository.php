<?php

namespace Sludio\HelperBundle\Script\Repository;

class QuickInsertRepository
{
    private static $mock = [];
    private static $metadata = [];
    private static $tableName;

    public static $em;
    public static $connection;
    public static $container;

    public static function init($no_fk_check = false, $manager = null)
    {
        global $kernel;

        if ('AppCache' === get_class($kernel)) {
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
        $data = self::extractExt($object, self::$em);

        self::$mock = $data['mock'];
        self::$tableName = $data['table'];
        self::$metadata[$data['table']] = $data['meta'];
    }

    public static function extractExt($object, $em)
    {
        $metadata = $em->getClassMetadata(get_class($object));

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
            'meta' => $metadata
        ];

        return $data;
    }

    public static function persist($object, $full = false, $extraFields = [], $no_fk_check = false, $manager = null, &$out = null)
    {
        self::init($no_fk_check, $manager);
        if(is_object($object)){
            self::extract($object);
            $tableName = self::$tableName;
        } else {
            $tableName = $object;
        }
        $id = self::findNextId($tableName);
        $keys = [];
        $values = [];

        $columns = self::$mock[$tableName];
        if(!empty($extraFields) && isset($extraFields[$tableName])) {
            $columns = array_merge(self::$mock[$tableName], $extraFields[$tableName]);
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
        if ($sql && $id) {
            if($out){
                $out = $sql;
            }
            $sth = self::$connection->prepare($sql);
            $sth->execute();
        }

        self::close($no_fk_check);
        return $id;
    }

    private static function buildExtra($tableName, $extra)
    {
        $methods = [
            'GROUP BY',
            'HAVING',
            'ORDER BY',
        ];
        $sql = '';

        foreach($methods as $method){
            if(isset($extra[$method])){
                $sql .= ' '.$method.' ';
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
                $sql .=  'LIMIT '.$offset.', '.$limit;
            }
        }

        $sql = str_replace('  ', ' ', $sql);

        return $sql;
    }

    private static function buildWhere($tableName, $where)
    {
        $whereSql = '';
        if ($where && is_array($where)) {
            $skip = false;
            foreach ($where as $key => $value) {
                $fk = $key;
                if(is_array($value)){
                    $skip = true;
                    $f = trim($value[0]);
                } else {
                    $f = trim($value);
                }
                break;
            }
            if(!$skip && isset(self::$mock[$tableName][$fk])) {
                if(is_numeric($f)){
                    $whereSql .= ' WHERE '.self::$mock[$tableName][$fk]." = $f";
                } else {
                    $whereSql .= ' WHERE '.self::$mock[$tableName][$fk]." = '".addslashes(trim($f))."'";
                }
            } else {
                if(!$skip && is_numeric($f)){
                    $whereSql .= ' WHERE '.$fk." = $f";
                } elseif(!$skip && !is_numeric($f)) {
                    $whereSql .= ' WHERE '.$fk." = '".addslashes(trim($f))."'";
                } elseif($skip && is_numeric($fk)){
                    $whereSql .= " WHERE $f";
                }
            }
            unset($where[$fk]);
            if ($where && is_array($where)) {
                foreach ($where as $key => $value) {
                    $skip = is_array($value);
                    if(!$skip && isset(self::$mock[$tableName][$key])) {
                        if(is_numeric($value)){
                            $whereSql .= ' AND '.self::$mock[$tableName][$key]." = $value";
                        } else {
                            $whereSql .= ' AND '.self::$mock[$tableName][$key]." = '".addslashes(trim($value))."'";
                        }
                    } else {
                        if(!$skip && is_numeric($value)){
                            $whereSql .= ' AND '.$key." = $value";
                        } elseif(!$skip && !is_numeric($f)) {
                            $whereSql .= ' AND '.$key." = '".addslashes(trim($value))."'";
                        } elseif($skip && is_numeric($key)){
                            $whereSql .= " AND {$value[0]}";
                        }
                    }
                }
            }
        }

        return $whereSql;
    }

    public static function get($object, $one = false, $where = [], $no_fk_check = false, $fields = [], $manager = null, $extra = [], &$out = null)
    {
        self::init($no_fk_check, $manager);
        if(is_object($object)){
            self::extract($object);
            $tableName = self::$tableName;
        } else {
            $tableName = $object;
        }
        $whereSql = self::buildWhere($tableName, $where);
        $select = (isset($extra['MODE']) ? 'SELECT '.$extra['MODE'] : 'SELECT').' ';
        if(!$fields){
            $sql = $select.'id FROM '.$tableName.' '.$whereSql;
        } else {
            $sql = $select.(implode(', ', $fields)).' FROM '.$tableName.' '.$whereSql;
        }
        if(!empty($extra)){
            $extraSql = self::buildExtra($tableName, $extra);
            $sql .= $extraSql;
        }
        if($out){
            $out = $sql;
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
        if($one || !$result) {
            return null;
        }

        $field = null;
        if(!$fields){
            $field = 'id';
        } elseif(count($fields) === 1 && $fields[0] !== '*'){
            $field = $fields[0];
        }

        if($field){
            foreach($result as &$res){
                $res = $res[$field];
            }
        }

        return $result;
    }

    public static function link($object, $data, $no_fk_check = false, $manager = null, &$out = null)
    {
        self::init($no_fk_check, $manager);
        if(is_object($object)){
            self::extract($object);
            $tableName = self::$tableName;
        } else {
            $tableName = $object;
        }
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
            if($out){
                $out = $sql;
            }
            $sth = self::$connection->prepare($sql);
            $sth->execute();
        }

        self::close($no_fk_check);
    }

    public static function update($id, $object, $extra = [], $no_fk_check = false, $manager = null, &$out = null)
    {
        self::init($no_fk_check, $manager);
        if(is_object($object)){
            self::extract($object);
            $tableName = self::$tableName;
        } else {
            $tableName = $object;
        }
        $sqls = "
            SELECT
                *
            FROM
                ".$tableName."
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
        $data = [];

        $columns = self::$mock[$tableName];
        if(!empty($extra) && isset($extra[$tableName])) {
            $columns = array_merge(self::$mock[$tableName], $extra[$tableName]);
        }

        $flip = array_flip($columns);
        foreach ($result as $key => $value) {
            $data[self::$mock[$tableName][$flip[$key]]] = $object->{'get'.ucfirst($flip[$key])}();
        }

        if ($data) {
            $sqlu = "
                UPDATE
                    ".$tableName."
                SET

            ";
            foreach ($data as $key => $value) {
                $meta = self::$metadata[$tableName]->getFieldMapping($flip[$key]);
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
            if($out){
                $out = $sql;
            }
            $sthu = self::$connection->prepare($sqlu);
            $sthu->execute();
        }

        self::close($no_fk_check);
    }

    public static function delete($object, $where = [], $no_fk_check = false, $manager = null, &$out = null)
    {
        self::init($no_fk_check, $manager);
        if(is_object($object)){
            self::extract($object);
            $tableName = self::$tableName;
        } else {
            $tableName = $object;
        }
        $whereSql = self::buildWhere($tableName, $where);
        $sql = 'DELETE FROM '.$tableName.' '.$whereSql;
        if($out){
            $out = $sql;
        }
        $sth = self::$connection->prepare($sql);
        $sth->execute();

        self::close($no_fk_check);
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
        if($out){
            $out = $sql;
        }
        $sth = self::$connection->prepare($sql);
        $sth->execute();
        $result = $sth->fetch();

        if (isset($result['AUTO_INCREMENT'])) {
            return (int) $result['AUTO_INCREMENT'];
        }

        return 1;
    }
}
