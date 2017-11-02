<?php

namespace Sludio\HelperBundle\Script\Repository;

use Sludio\HelperBundle\Script\Utils\Helper;

class QuickInsertRepository extends QuickInsertFunctions
{
    public static function findNextIdExt($object, $entityManager = null)
    {
        self::init();
        $data = self::extractExt($object, $entityManager);

        return self::findNextId($data['table']);
    }

    public static function findNextId($tableName)
    {
        return self::get(['table_name' => 'information_schema.tables'], true, [
            'table_name' => $tableName,
            ['table_schema = DATABASE()'],
        ], ['AUTO_INCREMENT'], null, []) ?: 1;
    }

    public static function setFK($fkCheck = 0, $noFkCheck = false)
    {
        if (!$noFkCheck) {
            self::runSQL("SET FOREIGN_KEY_CHECKS = $fkCheck", false, null, true);
        }
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
        if (substr($sql, 0, 6) === "SELECT") {
            return $sth->fetchAll();
        }
    }

    public static function get($object, $one = false, $where = [], $fields = [], $manager = null, $extra = [])
    {
        self::getTable($object, $tableName, $columns, $type, $manager);

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

    public static function persist($object, $full = false, $extraFields = [], $noFkCheck = false, $manager = null)
    {
        self::getTable($object, $tableName, $columns, $type, $manager, $extraFields);

        $id = self::findNextId($tableName);
        $data = [];

        $idd = null;
        foreach ($columns as $value => $key) {
            $keys = [
                $key,
                $value,
            ];
            if (!Helper::multiple($keys)) {
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

        self::runSQL($sql, $noFkCheck);

        return $id;
    }

    public static function update($id, $object, $extraFields = [], $noFkCheck = false, $manager = null)
    {
        self::getTable($object, $tableName, $columns, $type, $manager, $extraFields);

        $result = self::get(['table_name' => $tableName], true, ['id' => $id], ['*']);
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
                $intTypes = [
                    'boolean',
                    'integer',
                    'longint',
                ];
                if (in_array(self::$metadata[$tableName]->getFieldMapping($flip[$key])['type'], $intTypes)) {
                    $value = intval($value);
                } else {
                    $value = "'".addslashes(trim($value))."'";
                }
                $sql .= ' '.$key." = ".$value.",";
            }
            $sql = substr($sql, 0, -1).' WHERE id = '.$id;

            self::runSQL($sql, $noFkCheck);
        }
    }

    public static function delete($object, $where = [], $noFkCheck = false, $manager = null)
    {
        self::getTable($object, $tableName, $columns, $type, $manager);

        $sql = 'DELETE FROM '.$tableName.self::buildWhere($tableName, $where);
        self::runSQL($sql, $noFkCheck);
    }

    public static function link($object, $data, $noFkCheck = false, $manager = null)
    {
        self::getTable($object, $tableName, $columns, $type, $manager);

        $data['table_name'] = $tableName;
        self::persist($data, true, [], $noFkCheck, $manager);
    }
}
