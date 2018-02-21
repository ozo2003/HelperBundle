<?php

namespace Sludio\HelperBundle\Script\Repository;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;

class Filters
{
    public static function filterGetResult($result = null, array $fields = [], $one = false)
    {
        if ($result) {
            /** @var array $result */
            $field = (\count($fields) === 1 && $fields[0] !== '*') ? $fields[0] : null;
            if ($field !== null) {
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

    public static function getLimit(array $extra = [], &$sql)
    {
        if (isset($extra['LIMIT']) && \is_array($extra['LIMIT'])) {
            if (isset($extra['LIMIT'][1])) {
                list($offset, $limit) = $extra['LIMIT'];
            } else {
                $offset = 0;
                $limit = $extra['LIMIT'][0];
            }
            $sql = sprintf('%sLIMIT %s, %s', $sql, $offset, $limit);
        }
    }

    public static function extractExt(ClassMetadata $metadata)
    {
        $fields = $metadata->getFieldNames();
        $columns = $metadata->getColumnNames();
        $table = $metadata->getTableName();
        $identifier = null;

        $result = [];
        foreach ($fields as $key => $field) {
            /** @var $columns array */
            foreach ($columns as $key2 => $column) {
                if ($key === $key2) {
                    $result[$table][$field] = $column;
                    if ($field === $metadata->getIdentifier()[0]) {
                        $identifier = $column;
                    }
                }
            }
        }

        $data = [
            'mock' => $result,
            'table' => $table,
            'meta' => $metadata,
            'identifier' => $identifier,
        ];

        return $data;
    }
}
