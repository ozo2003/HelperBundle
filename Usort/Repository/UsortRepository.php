<?php

namespace Sludio\HelperBundle\Usort\Repository;

class UsortRepository
{
    public static $em;
    public static $connection;
    public static $container;

    public static function init()
    {
        global $kernel;

        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        self::$container = $kernel->getContainer();

        self::$em = self::$container->get('doctrine')->getManager(self::$container->getParameter('sludio_helper.entity.manager'));
        self::$connection = self::$em->getConnection();
        self::$database = self::$container->getParameter('sludio_helper.entity.database');
    }

    public static function findNextId2($object)
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

        self::close();
        return 1;
    }
    
    public static function findNextId($object)
    {
        self::init();
        self::extract($object);
        $sql = "
            SELECT 
                AUTO_INCREMENT
            FROM
                information_schema.tables
            WHERE
                table_name = '".self::$tableName."'
            AND
                table_schema = '".self::$database."'
        ";
        $sth = self::$connection->prepare($sql);
        $sth->execute();
        $result = $sth->fetch();

        if (isset($result['AUTO_INCREMENT'])) {
            return (int) $result['AUTO_INCREMENT'];
        }

        self::close();
        return 1;
    }
}
