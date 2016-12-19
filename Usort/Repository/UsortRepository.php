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

        self::$em = self::$container->get('doctrine')->getManager(self::$container->getParameter('sludio_helper.entity_manager'));
        self::$connection = self::$em->getConnection();
    }
    
    public static function findNextId($class)
    {
        self::init();
        $table = self::$em->getClassMetaData($class)->getTableName();
        $sql = "
            SHOW 
                TABLE STATUS 
            LIKE 
                '{$table}'
        ";
        $sth = self::$connection->prepare($sql);
        $sth->execute();
        $result = $sth->fetch();

        if (isset($result['Auto_increment'])) {
            return (int) $result['Auto_increment'];
        }

        return 1;
    }
}
