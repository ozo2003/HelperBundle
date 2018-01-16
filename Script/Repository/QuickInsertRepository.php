<?php

namespace Sludio\HelperBundle\Script\Repository;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Sludio\HelperBundle\Script\Utils\Helper;

class QuickInsertRepository extends QuickInsertFunctions
{
    public static function findNextIdExt(ClassMetadata $metadata, $manager = null)
    {
        self::init($manager);
        $data = self::extractExt($metadata);

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

        $result = self::runSQL($sql) ?: null;

        if ($result) {
            $field = null;
            if (\count($fields) === 1 && $fields[0] !== '*') {
                $field = $fields[0];
            }
            if ($field !== null) {
                if (!$one) {
                    /** @var $result array */
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
                if (self::numeric($tableName, $key, $value)) {
                    $value = (int)$value;
                } else {
                    $value = "'".addslashes(trim($value))."'";
                }
                $sql .= ' '.$key.' = '.$value.',';
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
        $data = [];

        $idd = null;
        /** @var $columns array */
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
                ('.implode(',', array_values($data)).')
        ';

        self::runSQL($sql, $noFkCheck);

        return $id;
    }
}
