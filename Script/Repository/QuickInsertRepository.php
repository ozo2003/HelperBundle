<?php

namespace Sludio\HelperBundle\Script\Repository;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Sludio\HelperBundle\Script\Utils\Helper;

class QuickInsertRepository extends QuickInsertFunctions
{
    public static function findNextIdExt(ClassMetadata $metadata, $manager = null)
    {
        self::init($manager);
        $data = Filters::extractExt($metadata);

        return self::findNextId($data['table']);
    }

    public static function findNextId($tableName)
    {
        return self::get(['table_name' => 'information_schema.tables'], true, [
            'table_name' => $tableName,
            ['table_schema = DATABASE()'],
        ], ['AUTO_INCREMENT']) ?: 1;
    }

    public static function get($object, $one = false, array $where = [], array $fields = [], $manager = null, array $extra = [])
    {
        self::getTable($object, $tableName, $columns, $type, $manager);

        $select = sprintf('SELECT %s ', isset($extra['MODE']) ? $extra['MODE'] : '');
        $fields = $fields ?: ['id'];
        $sql = $select.implode(', ', $fields).' FROM '.$tableName.self::buildWhere($tableName, $where).self::buildExtra($extra);

        return Filters::filterGetResult((self::runSQL($sql) ?: null), $fields, $one);
    }

    public static function runSQL($sql, $noFkCheck = true, $manager = null, $skip = false)
    {
        $sql = trim(preg_replace('/\s+/', ' ', $sql));
        self::init($manager);
        if (!$skip) {
            self::setFK(0, $noFkCheck);
        }

        $sth = self::$connection->prepare($sql);
        $sth->execute();

        if (!$skip) {
            self::setFK(1, $noFkCheck);
        }
        if (0 === strpos($sql, 'SELECT')) {
            return $sth->fetchAll();
        }

        return true;
    }

    public static function setFK($fkCheck = 0, $noFkCheck = false)
    {
        if (!$noFkCheck) {
            self::runSQL("SET FOREIGN_KEY_CHECKS = $fkCheck", false, null, true);
        }
    }

    public static function update($id, $object, array $extraFields = [], $noFkCheck = false, $manager = null)
    {
        self::getTable($object, $tableName, $columns, $type, $manager, $extraFields);

        $result = self::get(['table_name' => $tableName], true, ['id' => $id], ['*']) ?: [];
        if (isset($result['id'])) {
            unset($result['id']);
        }
        $data = self::parseUpdateResult($object, $type, $id, $tableName, $result);

        if (!empty($data)) {
            $sql = sprintf('UPDATE %s SET ', $tableName);
            foreach ($data as $key => $value) {
                $sql .= ' '.$key.' = '.self::slashes($tableName, $key, $value).',';
            }
            $sql = substr($sql, 0, -1).' WHERE id = '.$id;

            self::runSQL($sql, $noFkCheck);
        }
    }

    public static function delete($object, array $where = [], $noFkCheck = false, $manager = null)
    {
        self::getTable($object, $tableName, $columns, $type, $manager);

        $sql = sprintf('DELETE FROM %s%s', $tableName, self::buildWhere($tableName, $where));
        self::runSQL($sql, $noFkCheck);
    }

    public static function link($object, $data, $noFkCheck = false, $manager = null)
    {
        self::getTable($object, $tableName, $columns, $type, $manager);

        $data['table_name'] = $tableName;
        self::persist($data, true, [], $noFkCheck, $manager);
    }

    public static function persist($object, $full = false, array $extraFields = [], $noFkCheck = false, $manager = null)
    {
        self::getTable($object, $tableName, $columns, $type, $manager, $extraFields);

        $id = self::findNextId($tableName);
        $data = self::parsePersistColumns($columns, $object, $type, $tableName, $idd);

        if (!$full) {
            $data[self::$identifier] = $id;
        } else {
            $id = $idd;
        }

        if ($id !== null && Helper::isEmpty($data)) {
            return null;
        }

        $sql = '
            INSERT INTO
                '.$tableName.'
                    ('.implode(',', array_keys($data)).')
            VALUES
                ('.self::makeValues($tableName, $data).')
        ';

        self::runSQL($sql, $noFkCheck);

        return $id;
    }
}
