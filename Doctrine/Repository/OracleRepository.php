<?php

namespace Sludio\HelperBundle\Doctrine\Repository;

class OracleRepository {
    
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

        self::$em = self::$container->get('doctrine')->getManager(self::$container->getParameter('sludio_helper.entity.oracle_manager'));
        self::$connection = self::$em->getConnection();
    }
    
    
    
}
