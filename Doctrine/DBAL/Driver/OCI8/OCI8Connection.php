<?php

namespace Sludio\HelperBundle\Doctrine\DBAL\Driver\OCI8;

class OCI8Connection extends \Doctrine\DBAL\Driver\OCI8\OCI8Connection 
{
    public function getDbh()
    {
        return $this->dbh;
    }
}
